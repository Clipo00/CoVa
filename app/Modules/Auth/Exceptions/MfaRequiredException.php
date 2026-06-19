<?php

declare(strict_types=1);

namespace App\Modules\Auth\Exceptions;

use App\Modules\Auth\Models\User;

class MfaRequiredException extends \RuntimeException
{
    public function __construct(
        public readonly User $user,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message ?: __('auth.mfa_required'), $code, $previous);
    }
}
