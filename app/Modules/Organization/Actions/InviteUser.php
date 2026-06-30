<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationInvitation;
use App\Modules\Organization\Notifications\OrganizationInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteUser
{
    public function execute(
        Organization $organization,
        string $email,
        string $role = 'developer',
        int $expiresInHours = 48
    ): OrganizationInvitation {
        // Block inviting users who are already members of THIS organization.
        // The inviter already sees them in the members list — this is a
        // usability check, not an information disclosure risk.
        $isAlreadyMember = User::where('email', $email)
            ->whereHas('organizations', fn ($q) => $q->where('organization_id', $organization->id))
            ->exists();

        if ($isAlreadyMember) {
            throw ValidationException::withMessages([
                'email' => [__('organization.invite_already_member')],
            ]);
        }

        $token = Str::random(64);

        $invitation = OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'email' => $email,
            'token' => $token,
            'role' => $role,
            'expires_at' => now()->addHours($expiresInHours),
        ]);

        // Only send email if the user does NOT already exist in the system.
        // This prevents information disclosure: the inviter cannot determine
        // whether the email belongs to a user in another organization.
        $userExistsInSystem = User::where('email', $email)->exists();

        if (!$userExistsInSystem) {
            Notification::route('mail', $email)
                ->notify(new OrganizationInvitationNotification($invitation));
        }

        return $invitation;
    }
}
