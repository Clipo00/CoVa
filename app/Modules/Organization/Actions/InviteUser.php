<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationInvitation;
use App\Modules\Organization\Notifications\OrganizationInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteUser
{
    public function execute(
        Organization $organization,
        string $email,
        string $role = 'developer',
        int $expiresInHours = 48
    ): OrganizationInvitation {
        $token = Str::random(64);

        $invitation = OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'email' => $email,
            'token' => $token,
            'role' => $role,
            'expires_at' => now()->addHours($expiresInHours),
        ]);

        Notification::route('mail', $email)
            ->notify(new OrganizationInvitationNotification($invitation));

        return $invitation;
    }
}
