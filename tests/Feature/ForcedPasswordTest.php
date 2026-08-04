<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('redirects users with a temporary password to the required change page', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirectToRoute('password.force.edit');

    $this->actingAs($user)
        ->get(route('password.force.edit'))
        ->assertSuccessful()
        ->assertSee('Crie uma nova senha');
});

it('changes a temporary password and releases system access', function () {
    $user = User::factory()->mustChangePassword()->create([
        'password' => 'Temporary@1',
    ]);

    $this->actingAs($user)
        ->put(route('password.force.update'), [
            'current_password' => 'Temporary@1',
            'password' => 'Permanent@Password2',
            'password_confirmation' => 'Permanent@Password2',
        ])->assertRedirectToRoute('dashboard')
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->must_change_password)->toBeFalse()
        ->and($user->password_changed_at)->not->toBeNull()
        ->and(Hash::check('Permanent@Password2', $user->password))->toBeTrue();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('requires the current temporary password', function () {
    $user = User::factory()->mustChangePassword()->create([
        'password' => 'Temporary@1',
    ]);

    $this->actingAs($user)
        ->from(route('password.force.edit'))
        ->put(route('password.force.update'), [
            'current_password' => 'Wrong@Password1',
            'password' => 'Permanent@Password2',
            'password_confirmation' => 'Permanent@Password2',
        ])->assertRedirect(route('password.force.edit'))
        ->assertSessionHasErrors('current_password');

    expect($user->fresh()->must_change_password)->toBeTrue();
});

it('requires the new password to differ from the temporary password', function () {
    $user = User::factory()->mustChangePassword()->create([
        'password' => 'Temporary@1',
    ]);

    $this->actingAs($user)
        ->from(route('password.force.edit'))
        ->put(route('password.force.update'), [
            'current_password' => 'Temporary@1',
            'password' => 'Temporary@1',
            'password_confirmation' => 'Temporary@1',
        ])->assertRedirect(route('password.force.edit'))
        ->assertSessionHasErrors('password');

    expect($user->fresh()->must_change_password)->toBeTrue();
});
