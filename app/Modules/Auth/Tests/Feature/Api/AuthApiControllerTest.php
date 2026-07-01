<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Feature\Api;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiControllerTest extends TestCase
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
            'password' => Hash::make('password'),
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

    private function createProUserWithBlueprint(): array
    {
        [$user, $org] = $this->createProUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440400',
            'organization_id' => $org->id,
            'slug' => 'my-verify-bp',
            'title' => 'My Verify Blueprint',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $blueprint->variables()->createMany([
            [
                'key' => 'API_KEY',
                'type' => 'fixed',
                'default_value' => 'super-secret-value',
                'is_secret' => true,
                'is_interactive' => false,
                'section' => 'secrets',
                'sort_order' => 0,
            ],
            [
                'key' => 'DB_PASS',
                'type' => 'fixed',
                'default_value' => 'db-password-123',
                'is_secret' => true,
                'is_interactive' => false,
                'section' => 'secrets',
                'sort_order' => 1,
            ],
        ]);

        return [$user, $org, $blueprint];
    }

    private function createFreeUser(): User
    {
        return User::create([
            'name' => 'Free User',
            'email' => 'free-api@example.com',
            'password' => Hash::make('password'),
            'plan_id' => $this->freePlan->id,
        ]);
    }

    // --- GET /api/me ---

    public function test_me_returns_user_and_organizations(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertOk();
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'orgs' => [
                '*' => ['id', 'name', 'slug'],
            ],
        ]);

        $this->assertEquals($user->id, $response->json('user.id'));
        $this->assertEquals($user->name, $response->json('user.name'));
        $this->assertEquals($user->email, $response->json('user.email'));
        $this->assertCount(1, $response->json('orgs'));
        $this->assertEquals('Pro Org', $response->json('orgs.0.name'));
    }

    public function test_me_returns_multiple_organizations(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        $org2 = Organization::create([
            'slug' => 'second-org',
            'name' => 'Second Org',
            'owner_id' => $user->id,
        ]);
        $org2->members()->attach($user->id, ['role' => 'owner']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertOk();
        $this->assertCount(2, $response->json('orgs'));
        $orgNames = collect($response->json('orgs'))->pluck('name')->toArray();
        $this->assertContains('Pro Org', $orgNames);
        $this->assertContains('Second Org', $orgNames);
    }

    public function test_me_returns_401_without_auth(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertUnauthorized();
        $response->assertJson([
            'title' => 'Unauthorized',
            'status' => 401,
        ]);
    }

    // --- POST /api/fetch/{slug}/verify ---

    public function test_verify_password_returns_secrets(): void
    {
        [$user, $org, $blueprint] = $this->createProUserWithBlueprint();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/fetch/my-verify-bp/verify', [
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'secrets' => [
                '*' => ['key', 'value'],
            ],
        ]);

        $secrets = $response->json('secrets');
        $apiKey = collect($secrets)->firstWhere('key', 'API_KEY');
        $dbPass = collect($secrets)->firstWhere('key', 'DB_PASS');

        $this->assertNotNull($apiKey);
        $this->assertEquals('super-secret-value', $apiKey['value']);
        $this->assertNotNull($dbPass);
        $this->assertEquals('db-password-123', $dbPass['value']);
    }

    public function test_verify_password_returns_empty_secrets_when_no_secrets(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440401',
            'organization_id' => $org->id,
            'slug' => 'no-secrets-bp',
            'title' => 'No Secrets',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $blueprint->variables()->createMany([
            [
                'key' => 'APP_NAME',
                'type' => 'fixed',
                'default_value' => 'MyApp',
                'is_secret' => false,
                'is_interactive' => false,
                'section' => 'app',
                'sort_order' => 0,
            ],
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/fetch/no-secrets-bp/verify', [
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJson(['secrets' => []]);
    }

    public function test_verify_password_returns_403_on_wrong_password(): void
    {
        [$user, $org, $blueprint] = $this->createProUserWithBlueprint();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/fetch/my-verify-bp/verify', [
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'title' => 'Forbidden',
            'status' => 403,
            'detail' => 'Password verification failed',
        ]);
    }

    public function test_verify_password_returns_404_if_blueprint_not_found(): void
    {
        [$user, $org] = $this->createProUserWithOrg();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/fetch/non-existent-slug/verify', [
            'password' => 'password',
        ]);

        $response->assertNotFound();
        $response->assertJson([
            'title' => 'Not Found',
            'status' => 404,
        ]);
    }

    public function test_verify_password_requires_password_field(): void
    {
        [$user, $org, $blueprint] = $this->createProUserWithBlueprint();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/fetch/my-verify-bp/verify', []);

        $response->assertStatus(422);
    }

    public function test_verify_password_rate_limits_after_5_attempts(): void
    {
        [$user, $org, $blueprint] = $this->createProUserWithBlueprint();

        Sanctum::actingAs($user);

        // Send 5 attempts with wrong password
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/fetch/my-verify-bp/verify', [
                'password' => 'wrong-password',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/fetch/my-verify-bp/verify', [
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_verify_password_returns_401_without_auth(): void
    {
        $response = $this->postJson('/api/fetch/my-verify-bp/verify', [
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
    }
}
