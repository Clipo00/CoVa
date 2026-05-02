<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'laravel', 'name' => 'Laravel', 'description' => 'Proyectos PHP con Laravel'],
            ['slug' => 'nodejs', 'name' => 'Node.js', 'description' => 'Proyectos JavaScript con Node.js'],
            ['slug' => 'python', 'name' => 'Python', 'description' => 'Proyectos Python'],
            ['slug' => 'devops', 'name' => 'DevOps', 'description' => 'Configuración de infraestructura y CI/CD'],
            ['slug' => 'frontend', 'name' => 'Frontend', 'description' => 'Proyectos frontend con React, Vue, etc.'],
            ['slug' => 'mobile', 'name' => 'Mobile', 'description' => 'Proyectos móviles con React Native, Flutter, etc.'],
            ['slug' => 'database', 'name' => 'Database', 'description' => 'Configuración de bases de datos'],
            ['slug' => 'docker', 'name' => 'Docker', 'description' => 'Configuración de contenedores Docker'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'slug' => $category['slug'],
                'name' => $category['name'],
                'description' => $category['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
