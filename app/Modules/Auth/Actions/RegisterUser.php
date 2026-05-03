<?php

declare(strict_types=1);

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\DTOs\RegisterUserData;
use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Illuminate\Support\Facades\Hash;

class RegisterUser
{
    public function execute(RegisterUserData $data): User
    {
        $freePlan = Plan::where('slug', 'free')->first();

        if ($freePlan === null) {
            throw new \RuntimeException('Free plan does not exist. Run database seeders.');
        }

        $user = User::create([
            'name' => $data->name,
            'email' => (string) $data->email,
            'password' => Hash::make($data->password),
            'plan_id' => $freePlan->id,
        ]);

        return $user;
    }
}
