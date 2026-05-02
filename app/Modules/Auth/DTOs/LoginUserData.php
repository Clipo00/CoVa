<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class LoginUserData
{
    public readonly string $email;
    public readonly string $password;
    public readonly bool $remember;

    public function __construct(
        string $email,
        string $password,
        bool $remember = false
    ) {
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->email = $email;
        $this->password = $password;
        $this->remember = $remember;
    }
}
