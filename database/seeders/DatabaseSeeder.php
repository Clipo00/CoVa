<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AgentTemplateSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            TagSeeder::class,
            MarketplaceSeeder::class,
            AgentTemplateSeeder::class,
            TestUserSeeder::class,
        ]);
    }
}
