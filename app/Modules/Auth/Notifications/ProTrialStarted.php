<?php

declare(strict_types=1);

namespace App\Modules\Auth\Notifications;

use App\Modules\Auth\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProTrialStarted extends Notification
{
    use Queueable;

    public function __construct(
        public readonly \Illuminate\Support\Carbon $trialEndsAt,
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
                ->subject(__('landing.trial_started_subject'))
                ->greeting(__('landing.trial_started_greeting', ['name' => $notifiable->name]))
                ->line(__('landing.trial_started_intro'))
                ->line(__('landing.trial_started_features'))
                ->line(__('landing.trial_started_expiry', ['date' => $this->trialEndsAt->format('d/m/Y')]))
                ->salutation(__('auth.mfa_email_salutation'));
        } finally {
            app()->setLocale($originalLocale);
        }
    }
}
