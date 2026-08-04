<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

it('renders the guest authentication views', function (string $route, array $parameters = []) {
    $this->get(route($route, $parameters))->assertSuccessful();
})->with([
    'login' => ['login'],
    'forgot password' => ['password.request'],
    'reset password' => ['password.reset', ['token' => 'test-token']],
]);

it('uses the SIGAD identity on the login page', function () {
    $this->get(route('login'))
        ->assertSuccessful()
        ->assertSee('SIGAD')
        ->assertSee('Acesse sua conta')
        ->assertDontSee('PrismaBet');
});

it('does not expose public registration routes', function () {
    expect(Route::has('register'))->toBeFalse();

    $this->get('/register')->assertNotFound();
    $this->post('/register')->assertNotFound();
});

it('renders the authenticated authentication views', function (string $route, bool $verified) {
    $user = $verified
        ? User::factory()->create()
        : User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route($route))
        ->assertSuccessful();
})->with([
    'email verification' => ['verification.notice', false],
    'password confirmation' => ['password.confirm', true],
]);
