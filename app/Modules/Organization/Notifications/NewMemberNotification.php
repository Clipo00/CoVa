<?php

declare(strict_types=1);

namespace App\Modules\Organization\Notifications;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMemberNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Organization $organization,
        public readonly User $user,
        public readonly string $role,
        public readonly ?string $password = null,
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
        if ($this->password !== null) {
            return $this->newUserMail();
        }

        return $this->existingUserMail();
    }

    private function newUserMail(): MailMessage
    {
        return (new MailMessage)
            ->subject(__('organization.new_member_welcome_subject', [
                'organization' => $this->organization->name,
            ]))
            ->greeting(__('organization.new_member_welcome_greeting', [
                'name' => $this->user->name,
            ]))
            ->line(__('organization.new_member_welcome_intro', [
                'organization' => $this->organization->name,
                'role' => __('organization.role_' . $this->role),
            ]))
            ->line(__('organization.new_member_credentials_email', [
                'email' => $this->user->email,
            ]))
            ->line(__('organization.new_member_credentials_password', [
                'password' => $this->password,
            ]))
            ->line(__('organization.new_member_password_change'))
            ->action(__('organization.new_member_login_button'), route('login'))
            ->salutation(__('auth.mfa_email_salutation'));
    }

    private function existingUserMail(): MailMessage
    {
        return (new MailMessage)
            ->subject(__('organization.existing_member_added_subject', [
                'organization' => $this->organization->name,
            ]))
            ->greeting(__('organization.existing_member_added_greeting', [
                'name' => $this->user->name,
            ]))
            ->line(__('organization.existing_member_added_intro', [
                'organization' => $this->organization->name,
                'role' => __('organization.role_' . $this->role),
            ]))
            ->action(__('organization.existing_member_login_button'), route('login'))
            ->salutation(__('auth.mfa_email_salutation'));
    }
}
