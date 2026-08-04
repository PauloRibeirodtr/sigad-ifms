<?php

use App\Actions\Users\ChangeUserAccess;
use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

function extractTemporaryPassword(string $html): string
{
    preg_match('/<code data-temporary-password[^>]*>(.*?)<\/code>/s', $html, $matches);

    return html_entity_decode(trim($matches[1] ?? ''), ENT_QUOTES | ENT_HTML5);
}

it('lists and filters users only for administrators', function () {
    $administrator = User::factory()->administrator()->create();
    User::factory()->create(['name' => 'Maria Encontrada', 'email' => 'maria@example.com']);
    User::factory()->inactive()->create(['name' => 'Outro Servidor', 'email' => 'outro@example.com']);

    $this->actingAs($administrator)
        ->get(route('admin.users.index', ['nome' => 'Maria', 'perfil' => 'usuario', 'status' => 'ativo']))
        ->assertSuccessful()
        ->assertSee('Maria Encontrada')
        ->assertDontSee('Outro Servidor');

    $commonUser = User::factory()->create();

    $this->actingAs($commonUser)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('paginates the user listing', function () {
    $administrator = User::factory()->administrator()->create();
    User::factory()->count(11)->create();

    $this->actingAs($administrator)
        ->get(route('admin.users.index'))
        ->assertSuccessful()
        ->assertSee('12 usuários')
        ->assertSee('page=2', false);
});

it('creates a verified user with a one-time temporary password', function () {
    $administrator = User::factory()->administrator()->create();

    $response = $this->actingAs($administrator)->post(route('admin.users.store'), [
        'name' => 'Nova Servidora',
        'email' => 'NOVA@EXAMPLE.COM',
        'perfil' => UserProfile::Usuario->value,
        'ativo' => '1',
    ]);

    $response->assertSuccessful()
        ->assertSee('Senha temporária de Nova Servidora')
        ->assertSessionMissing('temporary_password');

    $temporaryPassword = extractTemporaryPassword($response->getContent());
    $user = User::query()->where('email', 'nova@example.com')->sole();

    expect($temporaryPassword)->toHaveLength(14)
        ->and($temporaryPassword)->toMatch('/[a-z]/')
        ->and($temporaryPassword)->toMatch('/[A-Z]/')
        ->and($temporaryPassword)->toMatch('/\d/')
        ->and($temporaryPassword)->toMatch('/[^a-zA-Z0-9]/')
        ->and(Hash::check($temporaryPassword, $user->password))->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->password_changed_at)->toBeNull()
        ->and($user->ativo)->toBeTrue()
        ->and($user->perfil)->toBe(UserProfile::Usuario);

    $this->actingAs($administrator)
        ->get(route('admin.users.index'))
        ->assertDontSee($temporaryPassword);
});

it('validates duplicate email when creating or editing users', function () {
    $administrator = User::factory()->administrator()->create();
    $firstUser = User::factory()->create(['email' => 'existing@example.com']);
    $secondUser = User::factory()->create(['email' => 'second@example.com']);

    $this->actingAs($administrator)
        ->post(route('admin.users.store'), [
            'name' => 'Duplicado',
            'email' => $firstUser->email,
            'perfil' => UserProfile::Usuario->value,
            'ativo' => '1',
        ])->assertSessionHasErrors('email');

    $this->actingAs($administrator)
        ->put(route('admin.users.update', $secondUser), [
            'name' => $secondUser->name,
            'email' => $firstUser->email,
        ])->assertSessionHasErrors('email');
});

it('updates only the basic user data', function () {
    $administrator = User::factory()->administrator()->create();
    $user = User::factory()->create();

    $this->actingAs($administrator)
        ->put(route('admin.users.update', $user), [
            'name' => 'Nome Atualizado',
            'email' => 'ATUALIZADO@EXAMPLE.COM',
            'perfil' => UserProfile::Administrador->value,
            'ativo' => '0',
        ])->assertRedirectToRoute('admin.users.index');

    $user->refresh();

    expect($user->name)->toBe('Nome Atualizado')
        ->and($user->email)->toBe('atualizado@example.com')
        ->and($user->perfil)->toBe(UserProfile::Usuario)
        ->and($user->ativo)->toBeTrue();
});

it('promotes and demotes another user', function () {
    $administrator = User::factory()->administrator()->create();
    $user = User::factory()->create();

    $this->actingAs($administrator)
        ->patch(route('admin.users.promote', $user))
        ->assertRedirectToRoute('admin.users.index');

    expect($user->refresh()->perfil)->toBe(UserProfile::Administrador);

    $this->actingAs($administrator)
        ->patch(route('admin.users.demote', $user))
        ->assertRedirectToRoute('admin.users.index');

    expect($user->refresh()->perfil)->toBe(UserProfile::Usuario);
});

it('activates and deactivates another user while invalidating database sessions', function () {
    config()->set('session.driver', 'database');

    $administrator = User::factory()->administrator()->create();
    $user = User::factory()->create();

    DB::table(config('session.table'))->insert([
        'id' => 'target-user-session',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'payload',
        'last_activity' => now()->timestamp,
    ]);

    $this->actingAs($administrator)
        ->patch(route('admin.users.deactivate', $user))
        ->assertRedirectToRoute('admin.users.index');

    expect($user->refresh()->ativo)->toBeFalse()
        ->and(DB::table(config('session.table'))->where('id', 'target-user-session')->exists())->toBeFalse();

    $this->actingAs($administrator)
        ->patch(route('admin.users.activate', $user))
        ->assertRedirectToRoute('admin.users.index');

    expect($user->refresh()->ativo)->toBeTrue();
});

it('does not allow an administrator to deactivate demote or reset their own account', function () {
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($administrator)
        ->patch(route('admin.users.deactivate', $administrator))
        ->assertForbidden();

    $this->actingAs($administrator)
        ->patch(route('admin.users.demote', $administrator))
        ->assertForbidden();

    $this->actingAs($administrator)
        ->post(route('admin.users.password.reset', $administrator))
        ->assertForbidden();
});

it('protects the last active administrator with a transactional access change', function () {
    $inactiveActor = User::factory()->administrator()->inactive()->create();
    $lastActiveAdministrator = User::factory()->administrator()->create();

    expect(fn () => app(ChangeUserAccess::class)->execute(
        $inactiveActor,
        $lastActiveAdministrator,
        active: false,
    ))->toThrow(ValidationException::class, 'último administrador ativo');

    expect($lastActiveAdministrator->refresh()->ativo)->toBeTrue();
});

it('resets another user password and displays it only in the response', function () {
    $administrator = User::factory()->administrator()->create();
    $user = User::factory()->create(['password' => 'Old@Password123']);

    $response = $this->actingAs($administrator)
        ->post(route('admin.users.password.reset', $user));

    $response->assertSuccessful()
        ->assertSee('Senha redefinida com sucesso')
        ->assertSessionMissing('temporary_password');

    $temporaryPassword = extractTemporaryPassword($response->getContent());
    $user->refresh();

    expect(Hash::check($temporaryPassword, $user->password))->toBeTrue()
        ->and(Hash::check('Old@Password123', $user->password))->toBeFalse()
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->password_changed_at)->toBeNull();
});

it('does not expose a route that deletes users', function () {
    expect(collect(app('router')->getRoutes()->getRoutes())
        ->contains(fn ($route) => in_array('DELETE', $route->methods(), true) && str_contains($route->uri(), 'admin/usuarios')))
        ->toBeFalse();
});
