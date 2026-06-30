<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Actions\Concerns\VerifiesPassword;
use App\Modules\Auth\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

final class CreateApiToken
{
    use VerifiesPassword;

    public function execute(User $user, string $name, Carbon $expiresAt, string $password, ?int $organizationId = null): string
    {
        if (!$user->hasApiAccess()) {
            throw new \RuntimeException('Your plan does not include API access.');
        }

        $this->verifyPassword($user, $password);

        if ($expiresAt->gt(now()->addYear())) {
            throw ValidationException::withMessages([
                'expires_at' => [__('auth.token_expiration_max')],
            ]);
        }

        $abilities = ['*'];

        // Store organization context as token ability for CLI scoping
        if ($organizationId !== null) {
            $abilities[] = 'org:' . $organizationId;
        }

        $newAccessToken = $user->createToken($name, $abilities, $expiresAt);

        return $newAccessToken->plainTextToken;
    }
}
