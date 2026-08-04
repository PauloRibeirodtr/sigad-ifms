<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

it('redirects guests from internal pages to login', function () {
    $this->get(route('dashboard'))
        ->assertRedirectToRoute('login');
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create([
        'password' => 'valid-password',
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'valid-password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create([
        'password' => 'valid-password',
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'invalid-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});

it('sends a password reset link for an existing user', function () {
    $user = User::factory()->create();
    Notification::fake();

    $this->from(route('password.request'))
        ->post(route('password.email'), [
            'email' => $user->email,
        ])->assertRedirect(route('password.request'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('redirects unverified users to the verification notice', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirectToRoute('verification.notice');
});
