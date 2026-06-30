<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\MfaCode;
use App\Modules\Auth\Models\User;

class VerifyMfaCode
{
    /**
     * Verify an MFA code for a given user.
     *
     * Returns true if the code is valid (exists, not expired, not used).
     * Marks the code as used upon successful verification.
     */
    public function execute(User $user, string $code): bool
    {
        $mfaCode = MfaCode::where('user_id', $user->id)
            ->where('code', $code)
            ->latest()
            ->first();

        if ($mfaCode === null) {
            return false;
        }

        if (!$mfaCode->isValid()) {
            return false;
        }

        $mfaCode->update(['used_at' => now()]);

        return true;
    }
}
