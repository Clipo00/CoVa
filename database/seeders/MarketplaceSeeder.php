<?php

namespace Database\Seeders;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar que no exista ya el system user
        if (User::where('email', 'system@covar.internal')->exists()) {
            $this->command->info('System user already exists, skipping.');

            return;
        }

        // Obtener el plan Enterprise (ya creado por PlanSeeder)
        $enterprisePlan = Plan::where('slug', 'enterprise')->first();

        if (!$enterprisePlan) {
            $this->command->error('No Enterprise plan found. Please run PlanSeeder first.');

            return;
        }

        // Crear el usuario sistema
        $systemUser = User::create([
            'name' => 'CoVa System',
            'email' => 'system@covar.internal',
            'password' => Hash::make(Str::random(64)),
            'plan_id' => $enterprisePlan->id,
            'is_system' => true,
        ]);

        // Crear la organización marketplace
        Organization::create([
            'slug' => 'cova-marketplace',
            'name' => 'CoVa Marketplace',
            'owner_id' => $systemUser->id,
        ]);

        $this->command->info('Marketplace organization created successfully.');
    }
}
