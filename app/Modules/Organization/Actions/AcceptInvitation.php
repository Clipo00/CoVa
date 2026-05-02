<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\OrganizationInvitation;
use Illuminate\Validation\ValidationException;

class AcceptInvitation
{
    public function execute(string $token, ?User $user = null): User
    {
        $invitation = OrganizationInvitation::where('token', $token)->first();

        if (!$invitation) {
            throw ValidationException::withMessages([
                'token' => ['Invitación no encontrada.'],
            ]);
        }

        if (!$invitation->isValid()) {
            throw ValidationException::withMessages([
                'token' => ['La invitación ha expirado o ya fue utilizada.'],
            ]);
        }

        if ($user === null) {
            if ($invitation->email === null) {
                throw ValidationException::withMessages([
                    'token' => ['Se requiere un usuario para aceptar esta invitación.'],
                ]);
            }

            $user = User::where('email', $invitation->email)->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['No existe un usuario con este email.'],
                ]);
            }
        }

        $user->organizations()->attach($invitation->organization_id, [
            'role' => $invitation->role,
        ]);

        $invitation->update(['used_at' => now()]);

        return $user;
    }
}
