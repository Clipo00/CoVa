<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\LoginUserData;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginUser
{
    public function execute(LoginUserData $data): User
    {
        $credentials = [
            'email' => $data->email,
            'password' => $data->password,
        ];

        if (!Auth::attempt($credentials, $data->remember)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no son correctas.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
