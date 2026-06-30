<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Actions\Concerns\VerifiesPassword;
use App\Modules\Auth\Models\User;

final class RevokeApiToken
{
    use VerifiesPassword;

    public function execute(User $user, int $tokenId, string $password): void
    {
        $this->verifyPassword($user, $password);

        $token = $user->tokens()->findOrFail($tokenId);

        $token->delete();
    }
}
