<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Notifications\NewMemberNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class CreateOrganizationUser
{
    public function execute(
        Organization $organization,
        string $name,
        string $email,
        string $role = 'developer',
        ?string $password = null,
    ): User {
        // Check if user with this email is already a member of the organization
        $existingMember = User::where('email', $email)
            ->whereHas('organizations', fn ($q) => $q->where('organization_id', $organization->id))
            ->first();

        if ($existingMember) {
            throw ValidationException::withMessages([
                'email' => [__('organization.user_already_member')],
            ]);
        }

        // Reuse existing user if they already have an account
        $user = User::where('email', $email)->first();
        $isNewUser = false;
        $plainPassword = null;

        if (!$user) {
            $isNewUser = true;
            $plainPassword = $password ?? bin2hex(random_bytes(8));

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $plainPassword,
                'password_change_required' => true,
                'plan_id' => null,
            ]);
        }

        $user->organizations()->attach($organization->id, [
            'role' => $role,
        ]);

        // Send appropriate notification
        if ($isNewUser) {
            Notification::route('mail', $email)
                ->notify(new NewMemberNotification(
                    organization: $organization,
                    user: $user,
                    role: $role,
                    password: $plainPassword,
                ));
        } else {
            Notification::route('mail', $email)
                ->notify(new NewMemberNotification(
                    organization: $organization,
                    user: $user,
                    role: $role,
                    password: null,
                ));
        }

        return $user;
    }
}
