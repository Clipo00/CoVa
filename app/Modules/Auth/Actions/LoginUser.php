<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\LoginUserData;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginUser
{
    public function execute(LoginUserData $data): User
    {
        $credentials = [
            'email' => $data->email,
            'password' => $data->password,
        ];

        if (!Auth::attempt($credentials, $data->remember)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.login_failed')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

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
