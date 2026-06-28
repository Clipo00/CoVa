<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Organization\Models\OrganizationInvitation;
use App\Modules\Organization\Notifications\OrganizationInvitationNotification;
use Illuminate\Support\Facades\Notification;

class ResendInvitation
{
    public function execute(OrganizationInvitation $invitation): void
    {
        // Reset expiry to 48 hours from now
        $invitation->update(['expires_at' => now()->addHours(48)]);

        // Re-send the invitation notification
        Notification::route('mail', $invitation->email)
            ->notify(new OrganizationInvitationNotification($invitation));
    }
}
