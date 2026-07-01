<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature\Api;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlueprintApiControllerTest extends TestCase
{
    use RefreshDatabase;

    private Plan $proPlan;
    private Plan $freePlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->proPlan = Plan::where('slug', 'pro')->first();
        $this->freePlan = Plan::where('slug', 'free')->first();
    }

    private function createProUserWithOrg(): array
    {
        $user = User::create([
            'name' => 'Pro User',
            'email' => 'pro-api@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->proPlan->id,
        ]);

        $organization = Organization::create([
            'slug' => 'pro-org',
            'name' => 'Pro Org',
            'owner_id' => $user->id,
        ]);

        $organization->members()->attach($user->id, ['role' => 'owner']);

        return [$user, $organization];
    }

    private function createFreeUser(): User
    {
        return User::create([
            'name' => 'Free User',
            'email' => 'free-api@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->freePlan->id,
        ]);
    }

    // --- GET /api/blueprints ---

    public function test_index_returns_paginated_blueprints(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        // Create 10 blueprints in the org
        for ($i = 1; $i <= 10; $i++) {
            Blueprint::create([
                'uuid' => '550e8400-e29b-41d4-a716-4466554400'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'organization_id' => $org->id,
                'slug' => 'blueprint-'.$i,
                'title' => 'Blueprint '.$i,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/blueprints?page=1&per_page=5');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'slug', 'title', 'description'],
            ],
            'meta' => ['current_page', 'total', 'per_page', 'last_page'],
        ]);

        $this->assertCount(5, $response->json('data'));
        $this->assertEquals(10, $response->json('meta.total'));
        $this->assertEquals(5, $response->json('meta.per_page'));
        $this->assertEquals(1, $response->json('meta.current_page'));
    }

    public function test_index_is_scoped_to_user_organizations(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        // Create another org that the user does NOT belong to
        $otherOwner = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->proPlan->id,
        ]);
        $otherOrg = Organization::create([
            'slug' => 'other-org',
            'name' => 'Other Org',
            'owner_id' => $otherOwner->id,
        ]);

        // Blueprint in user's org
        Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440100',
            'organization_id' => $org->id,
            'slug' => 'my-blueprint',
            'title' => 'My Blueprint',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        // Blueprint in other org (should NOT appear)
        Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440101',
            'organization_id' => $otherOrg->id,
            'slug' => 'other-blueprint',
            'title' => 'Other Blueprint',
            'tabs_config' => [],
            'created_by' => $otherOwner->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/blueprints');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('my-blueprint', $response->json('data.0.slug'));
        $this->assertEquals('My Blueprint', $response->json('data.0.title'));
    }

    public function test_index_returns_403_for_free_plan(): void
    {
        $user = $this->createFreeUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/blueprints');

        $response->assertForbidden();
        $response->assertJson([
            'title' => 'Forbidden',
            'status' => 403,
        ]);
    }

    public function test_index_returns_401_without_auth(): void
    {
        $response = $this->getJson('/api/blueprints');

        $response->assertUnauthorized();
    }

    public function test_index_second_page_returns_correct_items(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        for ($i = 1; $i <= 7; $i++) {
            Blueprint::create([
                'uuid' => '550e8400-e29b-41d4-a716-4466554402'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'organization_id' => $org->id,
                'slug' => 'bp-'.$i,
                'title' => 'BP '.$i,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/blueprints?page=2&per_page=5');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
        $this->assertEquals(7, $response->json('meta.total'));
        $this->assertEquals(2, $response->json('meta.current_page'));
    }

    // --- GET /api/blueprints/{slug} ---

    public function test_show_returns_resolved_blueprint(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440300',
            'organization_id' => $org->id,
            'slug' => 'my-resolved-bp',
            'title' => 'My Resolved Blueprint',
            'description' => 'A fully resolved blueprint',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'preset', 'name' => 'psr12'],
                        ],
                    ],
                ],
                [
                    'type' => 'vscode_extensions',
                    'config' => [
                        'segments' => [
                            ['name' => 'Tailwind CSS IntelliSense', 'id' => 'bradlc.vscode-tailwindcss'],
                            ['name' => 'ESLint', 'id' => 'dbaeumer.vscode-eslint'],
                        ],
                    ],
                ],
            ],
            'created_by' => $user->id,
        ]);

        $blueprint->variables()->createMany([
            [
                'key' => 'APP_NAME',
                'type' => 'fixed',
                'default_value' => 'MyApp',
                'is_secret' => false,
                'section' => 'app',
                'sort_order' => 0,
            ],
            [
                'key' => 'API_SECRET',
                'type' => 'fixed',
                'default_value' => 'should-be-masked',
                'is_secret' => true,
                'section' => 'secrets',
                'sort_order' => 1,
            ],
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/blueprints/my-resolved-bp');

        $response->assertOk();
        $response->assertJsonStructure([
            'uuid',
            'slug',
            'title',
            'description',
            'variables' => [
                '*' => ['key', 'type', 'default_value', 'is_secret', 'section'],
            ],
            'agent_md',
            'vscode_extensions',
            'vscode_install_command',
            'mcp_servers',
            'scripts',
            'scripts_shell',
        ]);

        // Verify secret masking
        $variables = $response->json('variables');
        $apiSecret = collect($variables)->firstWhere('key', 'API_SECRET');
        $this->assertTrue($apiSecret['is_secret']);
        $this->assertEquals('', $apiSecret['default_value']);

        $appName = collect($variables)->firstWhere('key', 'APP_NAME');
        $this->assertFalse($appName['is_secret']);
        $this->assertEquals('MyApp', $appName['default_value']);

        // Verify slug
        $this->assertEquals('my-resolved-bp', $response->json('slug'));
    }

    public function test_show_returns_404_for_nonexistent_slug(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/blueprints/non-existent-slug');

        $response->assertNotFound();
        $response->assertJson([
            'title' => 'Not Found',
            'status' => 404,
        ]);
    }

    public function test_show_returns_403_for_free_plan(): void
    {
        $user = $this->createFreeUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/blueprints/some-slug');

        $response->assertForbidden();
    }

    public function test_show_returns_401_without_auth(): void
    {
        $response = $this->getJson('/api/blueprints/some-slug');

        $response->assertUnauthorized();
    }
}
