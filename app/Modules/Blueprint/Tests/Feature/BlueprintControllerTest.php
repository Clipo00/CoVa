<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->withoutVite();
    }

    private function createUserWithOrg(): array
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $organization->members()->attach($user->id, ['role' => 'owner']);

        return [$user, $organization];
    }

    public function test_index_page_is_accessible(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $response = $this->actingAs($user)->get('/blueprints?org=' . $organization->id);

        $response->assertStatus(200);
        $response->assertSee('Blueprints');
    }

    public function test_create_page_is_accessible(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $response = $this->actingAs($user)->get('/blueprints/create?org=' . $organization->id);

        $response->assertStatus(200);
        $response->assertSee('Crear Blueprint');
    }

    public function test_show_page_displays_blueprint(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'organization_id' => $organization->id,
            'slug' => 'test-bp',
            'title' => 'Test Blueprint',
            'description' => 'A test blueprint',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/blueprints/' . $blueprint->uuid);

        $response->assertStatus(200);
        $response->assertSee('Test Blueprint');
    }
}
