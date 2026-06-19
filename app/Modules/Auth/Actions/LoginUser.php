<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\LoginUserData;
use App\Modules\Auth\Exceptions\MfaRequiredException;
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

        // If MFA is enabled, send a code and throw the MFA challenge exception
        if ($user->mfa_enabled) {
            $sendMfaCode = $this->sendMfaCode ?? app(SendMfaCode::class);
            $sendMfaCode->execute($user);

            throw new MfaRequiredException($user);
        }

        // MFA disabled — proceed with full login
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
}
