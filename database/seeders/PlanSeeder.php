<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Free',
                'description' => 'Plan gratuito para empezar',
                'max_organizations_per_user' => 2,
                'max_blueprints_per_org' => 3,
                'max_members_per_org' => 5,
                'max_variables_per_blueprint' => 50,
                'has_api_access' => false,
                'has_marketplace_publish' => false,
                'price_monthly' => 0.00,
                'is_active' => true,
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'description' => 'Plan profesional para equipos',
                'max_organizations_per_user' => 5,
                'max_blueprints_per_org' => 25,
                'max_members_per_org' => 50,
                'max_variables_per_blueprint' => 150,
                'has_api_access' => true,
                'has_marketplace_publish' => true,
                'price_monthly' => 9.99,
                'is_active' => true,
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'Plan empresarial sin límites',
                'max_organizations_per_user' => null,
                'max_blueprints_per_org' => null,
                'max_members_per_org' => null,
                'max_variables_per_blueprint' => null,
                'has_api_access' => true,
                'has_marketplace_publish' => true,
                'price_monthly' => null,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(
                ['slug' => $plan['slug']],
                array_merge($plan, [
                    'updated_at' => now(),
                    'created_at' => DB::table('plans')->where('slug', $plan['slug'])->value('created_at') ?? now(),
                ]),
            );
        }
    }
}
