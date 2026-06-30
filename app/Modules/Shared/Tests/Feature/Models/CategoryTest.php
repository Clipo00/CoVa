<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tests\Feature\Models;

use App\Modules\Shared\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_category(): void
    {
        $category = Category::create([
            'slug' => 'test-category',
            'name' => 'Test Category',
            'description' => 'A test category',
        ]);

        $this->assertDatabaseHas('categories', [
            'slug' => 'test-category',
            'name' => 'Test Category',
        ]);

        $this->assertEquals('A test category', $category->description);
    }

    public function test_seeders_create_categories(): void
    {
        $this->seed(CategorySeeder::class);

        $this->assertDatabaseCount('categories', 8);
        $this->assertDatabaseHas('categories', ['slug' => 'laravel']);
        $this->assertDatabaseHas('categories', ['slug' => 'docker']);
    }
}
