<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Models\MfaTrustedDevice;
use App\Modules\Auth\Models\User;

class TrustDevice
{
    /**
     * Trust the current device for 15 days.
     *
     * Creates a random token, stores its SHA-256 hash in the database
     * with a device fingerprint, and returns the plain token to be
     * stored in an HttpOnly cookie on the client.
     */
    public function execute(User $user): string
    {
        $token = bin2hex(random_bytes(32)); // 64 hex chars
        $fingerprint = $this->deviceFingerprint();

        // Revoke old device with same fingerprint (renew)
        MfaTrustedDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->delete();

        MfaTrustedDevice::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'device_fingerprint' => $fingerprint,
            'expires_at' => now()->addDays(15),
        ]);

        return $token;
    }

    /**
     * Generate a device fingerprint from User-Agent and IP subnet.
     *
     * The fingerprint changes if the User-Agent or IP subnet changes,
     * which happens on device or network switches.
     */
    public function deviceFingerprint(): string
    {
        $ua = request()->userAgent() ?? '';
        $ip = request()->ip();
        $subnet = substr($ip, 0, (int) strrpos($ip, '.'));

        return hash('sha256', $ua.'|'.($subnet ?: $ip));
    }
}
