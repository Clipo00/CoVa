<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\RegisterUserData;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function execute(RegisterUserData $data): User
    {
        $user = User::create([
            'name' => $data->name,
            'email' => (string) $data->email,
            'password' => Hash::make($data->password),
        ]);

        return $user;
    }
}
