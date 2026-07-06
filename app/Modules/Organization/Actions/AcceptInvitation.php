<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Exceptions\MaxMembersReachedException;
use App\Modules\Organization\Models\OrganizationInvitation;
use Illuminate\Validation\ValidationException;

class AcceptInvitation
{
    public function execute(string $token, ?User $user = null): User
    {
        $invitation = OrganizationInvitation::where('token', $token)->first();

        if (!$invitation) {
            throw ValidationException::withMessages([
                'token' => [__('organization.invitation_not_found')],
            ]);
        }

        if (!$invitation->isValid()) {
            throw ValidationException::withMessages([
                'token' => [__('organization.invitation_expired')],
            ]);
        }

        if ($user === null) {
            if ($invitation->email === null) {
                throw ValidationException::withMessages([
                    'token' => [__('organization.invitation_user_required')],
                ]);
            }

            $user = User::where('email', $invitation->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => [__('organization.invitation_no_user')],
                ]);
            }
        } else {
            // Si se pasa un usuario explícitamente, su email debe coincidir con la invitación
            if ($user->email !== $invitation->email) {
                throw ValidationException::withMessages([
                    'email' => [__('organization.invitation_email_mismatch')],
                ]);
            }
        }

        // Verificar límite de miembros de la organización
        $organization = $invitation->organization;
        $plan = $organization->plan;
        $maxMembers = $plan->max_members_per_org;

        if ($maxMembers !== null && $organization->members()->count() >= $maxMembers) {
            throw new MaxMembersReachedException($maxMembers, $plan->name);
        }

        $user->organizations()->attach($invitation->organization_id, [
            'role' => $invitation->role,
        ]);

        $invitation->update(['used_at' => now()]);

        return $user;
    }
}
