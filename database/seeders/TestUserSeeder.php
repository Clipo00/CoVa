<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $freePlan = Plan::where('slug', 'free')->firstOrFail();
        $proPlan = Plan::where('slug', 'pro')->firstOrFail();

        // Test user with Free plan (for basic feature testing)
        User::updateOrCreate(
            ['email' => 'admin@covar.dev'],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'plan_id' => $freePlan->id,
                'locale' => 'es',
                'onboarding_completed_at' => now(),
            ]
        );

        // Test user with Pro plan (for API token and marketplace testing)
        User::updateOrCreate(
            ['email' => 'pro@covar.dev'],
            [
                'name' => 'Pro Test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'plan_id' => $proPlan->id,
                'locale' => 'es',
                'onboarding_completed_at' => now(),
            ]
        );
    }
}
