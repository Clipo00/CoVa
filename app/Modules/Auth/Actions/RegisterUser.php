<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\RegisterUserData;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function execute(RegisterUserData $data): User
    {
        $freePlan = Plan::where('slug', 'free')->first();

        if ($freePlan === null) {
            throw new \RuntimeException('Free plan does not exist. Run database seeders.');
        }

        // Heredar locale de la cookie si el usuario eligió idioma antes de registrarse
        $cookieLocale = request()->cookie('locale');
        $locale = $cookieLocale && in_array($cookieLocale, ['es', 'en'], true) ? $cookieLocale : null;

        $user = User::create([
            'name' => $data->name,
            'email' => (string) $data->email,
            'locale' => $locale,
            'password' => Hash::make($data->password),
            'plan_id' => $freePlan->id,
        ]);

        event(new Registered($user));

        return $user;
    }
}
