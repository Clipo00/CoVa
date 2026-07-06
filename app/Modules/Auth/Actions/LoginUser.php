<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\LoginUserData;
use App\Modules\Auth\Exceptions\MfaRequiredException;
use App\Modules\Auth\Models\MfaTrustedDevice;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginUser
{
    public function __construct(
        private readonly ?SendMfaCode $sendMfaCode = null,
    ) {}

    public function execute(LoginUserData $data): User
    {
        $credentials = [
            'email' => $data->email,
            'password' => $data->password,
        ];

        // First validate credentials without logging in
        if (!Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.login_failed')],
            ]);
        }

        // Resolve the user from validated credentials
        $user = User::where('email', $data->email)->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => [__('auth.login_failed')],
            ]);
        }

        // If MFA is enabled, check for a trusted device before challenging
        if ($user->mfa_enabled) {
            $trustedDevice = $this->findTrustedDevice($user);

            if ($trustedDevice === null) {
                // No trusted device — send code and challenge
                $sendMfaCode = $this->sendMfaCode ?? app(SendMfaCode::class);
                $sendMfaCode->execute($user);

                throw new MfaRequiredException($user);
            }
            // Trusted device found — proceed with login (skip MFA)
        }

        // MFA disabled OR trusted device — proceed with full login
        Auth::login($user, $data->remember);

        // Si el usuario eligió idioma como invitado (cookie) y no tiene locale en BD,
        // guardarlo como preferencia permanente al loguearse
        if (!$user->locale) {
            $cookieLocale = request()->cookie('locale');
            if ($cookieLocale && in_array($cookieLocale, ['es', 'en'], true)) {
                $user->update(['locale' => $cookieLocale]);
            }
        }

        return $user;
    }

    /**
     * Check if the current request carries a valid trusted-device cookie.
     *
     * Returns the MfaTrustedDevice record if found and valid, or null otherwise.
     */
    private function findTrustedDevice(User $user): ?MfaTrustedDevice
    {
        $token = request()->cookie('mfa_trusted_device');

        if ($token === null) {
            return null;
        }

        $fingerprint = app(TrustDevice::class)->deviceFingerprint();
        $tokenHash = hash('sha256', $token);

        $device = MfaTrustedDevice::where('user_id', $user->id)
            ->where('token_hash', $tokenHash)
            ->first();

        if ($device === null || !$device->isValid($fingerprint)) {
            return null;
        }

        return $device;
    }
}
