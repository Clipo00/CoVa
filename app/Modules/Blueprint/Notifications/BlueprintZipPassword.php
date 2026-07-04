<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BlueprintZipPassword extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $blueprintTitle,
        public readonly string $password,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('blueprint.zip_password_subject', ['title' => $this->blueprintTitle]))
            ->greeting(__('blueprint.zip_password_greeting'))
            ->line(__('blueprint.zip_password_intro', ['title' => $this->blueprintTitle]))
            ->line($this->password)
            ->line(__('blueprint.zip_password_warning'))
            ->salutation(__('auth.mfa_email_salutation'));
    }
}
