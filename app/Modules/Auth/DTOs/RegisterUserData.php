<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTOs;

use App\Modules\Shared\ValueObjects\Email;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class RegisterUserData
{
    public readonly string $name;
    public readonly Email $email;
    public readonly string $password;

    public function __construct(
        string $name,
        string $email,
        string $password
    ) {
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'indisposable'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->name = $name;
        $this->email = new Email($email);
        $this->password = $password;
    }
}
