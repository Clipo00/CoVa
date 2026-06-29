<?php

declare(strict_types=1);

namespace App\Modules\Auth\Notifications;

use App\Modules\Auth\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $token,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject(__('auth.password_reset_subject'))
            ->greeting(__('auth.password_reset_greeting', ['name' => $notifiable->name]))
            ->line(__('auth.password_reset_intro'))
            ->action(__('auth.password_reset_button'), $url)
            ->line(__('auth.password_reset_expiry', ['count' => config('auth.passwords.users.expire', 60)]))
            ->line(__('auth.password_reset_no_action'))
            ->salutation(__('auth.mfa_email_salutation'));
    }
}
