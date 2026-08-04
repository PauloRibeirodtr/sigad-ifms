<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $token) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        /** @var User $notifiable */
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        $expirationMinutes = (int) config('auth.passwords.users.expire');

        return (new MailMessage)
            ->subject('Redefinição de senha — SIGAD')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Recebemos uma solicitação para redefinir a senha da sua conta no SIGAD.')
            ->action('Redefinir minha senha', $resetUrl)
            ->line("Este link expira em {$expirationMinutes} minutos.")
            ->line('Se você não solicitou a redefinição, ignore esta mensagem. Sua senha permanecerá inalterada.');
    }
}
