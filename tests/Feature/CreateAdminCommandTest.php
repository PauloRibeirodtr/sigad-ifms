<?php

use App\Enums\UserProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates the first active and verified administrator', function () {
    $this->artisan('app:create-admin')
        ->expectsQuestion('Nome', 'Administrador Inicial')
        ->expectsQuestion('E-mail', 'ADMIN@EXAMPLE.COM')
        ->expectsQuestion('Senha', 'Secure@Admin123')
        ->expectsQuestion('Confirme a senha', 'Secure@Admin123')
        ->expectsOutput('Administrador criado com sucesso.')
        ->assertSuccessful();

    $administrator = User::query()->sole();

    expect($administrator->name)->toBe('Administrador Inicial')
        ->and($administrator->email)->toBe('admin@example.com')
        ->and($administrator->perfil)->toBe(UserProfile::Administrador)
        ->and($administrator->ativo)->toBeTrue()
        ->and($administrator->must_change_password)->toBeFalse()
        ->and($administrator->email_verified_at)->not->toBeNull()
        ->and($administrator->password_changed_at)->not->toBeNull()
        ->and(Hash::check('Secure@Admin123', $administrator->password))->toBeTrue();
});

it('does not create another administrator through the bootstrap command', function () {
    User::factory()->administrator()->create();

    $this->artisan('app:create-admin')
        ->expectsOutput('Já existe uma conta administrativa cadastrada.')
        ->assertFailed();

    expect(User::query()->count())->toBe(1);
});
