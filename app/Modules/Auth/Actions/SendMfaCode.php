<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\MfaCode;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Notifications\MfaCodeNotification;
use Illuminate\Support\Facades\Notification;

class SendMfaCode
{
    /**
     * Generate a 6-digit MFA code, persist it, and send it via email notification.
     */
    public function execute(User $user): MfaCode
    {
        $code = (string) random_int(100000, 999999);

        $mfaCode = MfaCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        Notification::send($user, new MfaCodeNotification($code));

        return $mfaCode;
    }
}
