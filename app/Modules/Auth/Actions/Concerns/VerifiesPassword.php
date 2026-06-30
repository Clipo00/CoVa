<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions\Concerns;

use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

trait VerifiesPassword
{
    private function verifyPassword(User $user, string $password): void
    {
        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('auth.wrong_password')],
            ]);
        }
    }
}
