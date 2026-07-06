<?php

declare(strict_types=1);

namespace App\Modules\Auth\Notifications;

use App\Modules\Auth\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MfaCodeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $code,
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
        $originalLocale = app()->getLocale();
        app()->setLocale($notifiable->locale ?? config('app.locale', 'en'));

        try {
            return (new MailMessage)
                ->subject(__('auth.mfa_email_subject'))
                ->greeting(__('auth.mfa_email_greeting', ['name' => $notifiable->name]))
                ->line(__('auth.mfa_email_intro'))
                ->line($this->code)
                ->line(__('auth.mfa_email_expiry'))
                ->salutation(__('auth.mfa_email_salutation'));
        } finally {
            app()->setLocale($originalLocale);
        }
    }
}
