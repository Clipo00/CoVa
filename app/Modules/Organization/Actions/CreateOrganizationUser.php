<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Actions\RegisterUser;
use App\Modules\Auth\DTOs\RegisterUserData;
use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Support\Facades\Hash;

class CreateOrganizationUser
{
    public function execute(
        Organization $organization,
        string $name,
        string $email,
        string $role = 'developer'
    ): User {
        $temporaryPassword = bin2hex(random_bytes(8));

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($temporaryPassword),
            'plan_id' => null,
        ]);

        $user->organizations()->attach($organization->id, [
            'role' => $role,
        ]);

        return $user;
    }
}
