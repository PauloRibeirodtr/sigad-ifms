<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;

it('does not authenticate an inactive user', function () {
    $user = User::factory()->inactive()->create([
        'password' => 'Valid@Password1',
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'Valid@Password1',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('ends an existing session when the user becomes inactive', function () {
    $user = User::factory()->inactive()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirectToRoute('login');

    $this->assertGuest();
});

it('does not send password reset notifications to inactive users', function () {
    $user = User::factory()->inactive()->create();
    Notification::fake();

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => $user->email])
        ->assertRedirect(route('password.request'))
        ->assertSessionHasErrors('email');

    Notification::assertNothingSent();
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
});

it('forbids common users from the administrative area', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.index'))
        ->assertForbidden();
});

it('allows administrators into the administrative area', function () {
    $administrator = User::factory()->administrator()->create();

    $this->actingAs($administrator)
        ->get(route('admin.index'))
        ->assertSuccessful()
        ->assertSee('Administração de usuários');
});
