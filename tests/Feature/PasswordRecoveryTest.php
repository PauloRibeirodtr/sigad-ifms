<?php

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('creates a reset token and sends the SIGAD notification to an active user', function () {
    $user = User::factory()->create();
    Notification::fake();

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => mb_strtoupper($user->email)])
        ->assertRedirect(route('password.request'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    Notification::assertSentTo(
        $user,
        ResetPasswordNotification::class,
        fn (ResetPasswordNotification $notification): bool => $notification->token !== '',
    );
});

it('builds a branded password reset email with expiration and account address', function () {
    $user = User::factory()->create([
        'name' => 'Ana Docente',
        'email' => 'ana@example.com',
    ]);
    $mail = (new ResetPasswordNotification('token-seguro'))->toMail($user);

    expect($mail->subject)->toBe('Redefinição de senha — SIGAD')
        ->and($mail->greeting)->toBe('Olá, Ana Docente!')
        ->and($mail->actionText)->toBe('Redefinir minha senha')
        ->and($mail->actionUrl)->toContain('/reset-password/token-seguro')
        ->and($mail->actionUrl)->toContain('email=ana%40example.com')
        ->and(implode(' ', [...$mail->introLines, ...$mail->outroLines]))->toContain('60 minutos');
});

it('resets an active users password and clears the temporary password state', function () {
    $user = User::factory()->mustChangePassword()->create([
        'password' => 'Old@Password123',
    ]);
    $token = Password::broker()->createToken($user);

    $this->post(route('password.update'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'New@Password123',
        'password_confirmation' => 'New@Password123',
    ])->assertRedirect(route('login'))
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect(Hash::check('New@Password123', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeFalse()
        ->and($user->password_changed_at)->not->toBeNull();
});

it('rejects a previously issued token if the user becomes inactive', function () {
    $user = User::factory()->create([
        'password' => 'Old@Password123',
    ]);
    $token = Password::broker()->createToken($user);
    $user->update(['ativo' => false]);

    $this->from(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'New@Password123',
            'password_confirmation' => 'New@Password123',
        ])->assertRedirect(route('password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertSessionHasErrors('email');

    expect(Hash::check('Old@Password123', $user->fresh()->password))->toBeTrue();
});

it('keeps SMTP credentials outside versioned configuration', function () {
    $smtp = config('mail.mailers.smtp');

    expect($smtp)->toHaveKeys(['transport', 'scheme', 'host', 'port', 'username', 'password', 'timeout'])
        ->and($smtp['transport'])->toBe('smtp')
        ->and($smtp['timeout'])->toBe(10);
});
