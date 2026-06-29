<?php

declare(strict_types=1);

namespace App\Modules\Organization\Notifications;

use App\Modules\Organization\Models\OrganizationInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrganizationInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly OrganizationInvitation $invitation,
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
        $url = route('invitations.show', $this->invitation->token);

        return (new MailMessage)
            ->subject(__('organization.invitation_subject', [
                'organization' => $this->invitation->organization->name,
            ]))
            ->greeting(__('organization.invitation_greeting'))
            ->line(__('organization.invitation_intro', [
                'organization' => $this->invitation->organization->name,
                'role' => __('organization.role_' . $this->invitation->role),
            ]))
            ->action(__('organization.invitation_accept_button'), $url)
            ->line(__('organization.invitation_expiry', [
                'hours' => 48,
            ]))
            ->salutation(__('auth.mfa_email_salutation'));
    }
}
