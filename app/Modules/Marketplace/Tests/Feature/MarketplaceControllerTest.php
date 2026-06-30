<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use App\Modules\Shared\ValueObjects\Uuid;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Marketplace Test',
            'email' => 'marketplace@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $this->organization = $createOrg->execute($this->user, 'Marketplace Org', 'marketplace-org');
    }

    // 2.1.1: Show page displays blueprint content
    public function test_show_page_displays_blueprint(): void
    {
        $blueprint = $this->createPublicBlueprint([
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => [
                    'presets' => ['laravel-conventions'],
                    'skills' => [],
                    'custom_rules' => 'Rule from marketplace.',
                ]],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketplace.show', $blueprint->uuid));

        $response->assertStatus(200);
        $response->assertSee('Marketplace Show BP');
        $response->assertSee('Rule from marketplace.');
    }

    // 2.1.2: 404 for private blueprint
    public function test_404_for_private_blueprint(): void
    {
        $blueprint = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $this->organization->id,
            'slug' => 'private-bp-'.uniqid(),
            'title' => 'Private BP',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('marketplace.show', $blueprint->uuid));

        $response->assertStatus(404);
    }

    // 2.1.3: Secrets masked for non-owner
    public function test_secrets_masked_for_non_owner(): void
    {
        $blueprint = $this->createPublicBlueprint([]);

        BlueprintVariable::create([
            'blueprint_id' => $blueprint->id,
            'key' => 'SECRET_KEY',
            'type' => 'fixed',
            'default_value' => 'super-secret-123',
            'is_secret' => true,
        ]);

        BlueprintVariable::create([
            'blueprint_id' => $blueprint->id,
            'key' => 'PUBLIC_KEY',
            'type' => 'fixed',
            'default_value' => 'public-value',
            'is_secret' => false,
        ]);

        // Act as a different user (not the owner)
        $plan = Plan::where('slug', 'free')->first();
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('marketplace.show', $blueprint->uuid));

        $response->assertStatus(200);
        $response->assertSee('SECRET_KEY');
        $response->assertDontSee('super-secret-123');
        $response->assertSee('PUBLIC_KEY');
        $response->assertSee('public-value');
        $response->assertSee(__('blueprint.secret_value'));
    }

    // 2.1.4a: Subscribe button visible for auth users
    public function test_subscribe_button_visible_for_authenticated_user(): void
    {
        $blueprint = $this->createPublicBlueprint([]);

        $response = $this->actingAs($this->user)
            ->get(route('marketplace.show', $blueprint->uuid));

        $response->assertStatus(200);
        $response->assertSee(__('marketplace.subscribe_button'));
    }

    // 2.1.4b: Guest sees login link instead of subscribe button
    public function test_guest_sees_login_link_instead_of_subscribe_button(): void
    {
        $blueprint = $this->createPublicBlueprint([]);

        $response = $this->get(route('marketplace.show', $blueprint->uuid));

        $response->assertStatus(200);
        $response->assertDontSee(__('marketplace.subscribe_button'));
        $response->assertSee(__('marketplace.login_to_subscribe'));
    }

    // 2.1.4c: Show page displays stats (votes and subscribers)
    public function test_show_page_displays_stats(): void
    {
        $blueprint = $this->createPublicBlueprint([]);
        $blueprint->votes_count = 7;
        $blueprint->subscribers_count = 3;
        $blueprint->save();

        $response = $this->actingAs($this->user)
            ->get(route('marketplace.show', $blueprint->uuid));

        $response->assertStatus(200);
        $response->assertSee('7');
        $response->assertSee('3');
    }

    // 2.1.4d: Show page displays organization name
    public function test_show_page_displays_organization_name(): void
    {
        $blueprint = $this->createPublicBlueprint([]);

        $response = $this->actingAs($this->user)
            ->get(route('marketplace.show', $blueprint->uuid));

        $response->assertStatus(200);
        $response->assertSee('Marketplace Org');
    }

    /**
     * Create a public blueprint directly (no auth required).
     */
    private function createPublicBlueprint(array $extra = []): Blueprint
    {
        return Blueprint::create(array_merge([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $this->organization->id,
            'slug' => 'marketplace-bp-'.uniqid(),
            'title' => 'Marketplace Show BP',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $this->user->id,
        ], $extra));
    }
}
