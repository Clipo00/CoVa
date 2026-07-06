<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Livewire\MarketplaceList;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\MarketplaceSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPolishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->withoutVite();
    }

    private function createUserWithPlan(): User
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
            // Set onboarding as completed (backfilled for existing users)
            'onboarding_completed_at' => now(),
        ]);

        return $user;
    }

    private function createOrganization(User $user, string $slug, string $name = 'Test Org'): Organization
    {
        $organization = Organization::create([
            'slug' => $slug,
            'name' => $name,
            'owner_id' => $user->id,
        ]);

        $organization->members()->attach($user->id, ['role' => 'owner']);

        return $organization;
    }

    // ========================================================================
    // 1.1 Stats row visible with 4 correct counts
    // ========================================================================

    public function test_stats_row_shows_4_correct_counts_when_user_has_organizations(): void
    {
        $user = $this->createUserWithPlan();
        $org1 = $this->createOrganization($user, 'org-alpha', 'Org Alpha');
        $org2 = $this->createOrganization($user, 'org-beta', 'Org Beta');

        // Create 3 blueprints in org1
        for ($i = 1; $i <= 3; $i++) {
            Blueprint::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $org1->id,
                'slug' => 'bp-alpha-'.$i,
                'title' => 'Alpha BP '.$i,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        // Create 2 blueprints in org2
        for ($i = 1; $i <= 2; $i++) {
            Blueprint::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $org2->id,
                'slug' => 'bp-beta-'.$i,
                'title' => 'Beta BP '.$i,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        // Add 3 favorites
        $favorite1 = Blueprint::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $org1->id,
            'slug' => 'fav-bp-1',
            'title' => 'Favorite 1',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $favorite2 = Blueprint::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $org1->id,
            'slug' => 'fav-bp-2',
            'title' => 'Favorite 2',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $favorite3 = Blueprint::create([
            'uuid' => (string) Str::uuid(),
            'organization_id' => $org2->id,
            'slug' => 'fav-bp-3',
            'title' => 'Favorite 3',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $user->favoriteBlueprints()->attach([$favorite1->id, $favorite2->id, $favorite3->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            __('dashboard.stats_organizations'),
            '2',
            __('dashboard.stats_blueprints'),
            '5',
            __('dashboard.stats_favorites'),
            '3',
            __('dashboard.stats_plan'),
        ]);
    }

    // ========================================================================
    // 1.2 Stats row hidden when user has zero orgs
    // ========================================================================

    public function test_stats_row_not_rendered_when_user_has_zero_organizations(): void
    {
        $user = $this->createUserWithPlan();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee(__('dashboard.stats_favorites'));
        $response->assertDontSee(__('dashboard.stats_plan'));
        $response->assertSee(__('dashboard.empty_cta'));
    }

    // ========================================================================
    // 1.3 Org card displays blueprint and member counts
    // ========================================================================

    public function test_org_card_displays_blueprint_and_member_counts(): void
    {
        $user = $this->createUserWithPlan();
        $org = $this->createOrganization($user, 'my-org', 'My Org');

        // Create 3 blueprints
        for ($i = 1; $i <= 3; $i++) {
            Blueprint::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $org->id,
                'slug' => 'bp-'.$i,
                'title' => 'BP '.$i,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        // Add 5 members (including the owner, we need 4 more)
        for ($i = 1; $i <= 4; $i++) {
            $member = User::create([
                'name' => 'Member '.$i,
                'email' => 'member'.$i.'@example.com',
                'password' => bcrypt('password'),
                'plan_id' => $user->plan_id,
            ]);
            $org->members()->attach($member->id, ['role' => 'developer']);
        }

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        // The org card should show 3 blueprints and 5 members
        $response->assertSee('3');
        $response->assertSee('5');
    }

    // ========================================================================
    // 1.4 Marketplace empty state renders rich card
    // ========================================================================

    public function test_marketplace_empty_state_renders_rich_card(): void
    {
        $this->seed(MarketplaceSeeder::class);

        // Authenticate so the CTA can show
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Market Tester',
            'email' => 'market@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        Livewire::actingAs($user)->test(MarketplaceList::class)
            ->assertSee(__('marketplace.empty_heading'))
            ->assertSee(__('marketplace.empty_description'))
            ->assertSee(__('marketplace.empty_cta'));
    }

    // ========================================================================
    // 1.5 Blueprint index heading includes count badge
    // ========================================================================

    public function test_blueprint_index_heading_shows_total_count_badge(): void
    {
        $user = $this->createUserWithPlan();
        $org = $this->createOrganization($user, 'bp-index-org', 'BP Index Org');

        // Create 12 blueprints
        for ($i = 1; $i <= 12; $i++) {
            Blueprint::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $org->id,
                'slug' => 'total-bp-'.$i,
                'title' => 'Total BP '.$i,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        $response = $this->actingAs($user)->get(route('blueprints.index'));

        $response->assertStatus(200);
        $response->assertSee('12');
        // Verify the pill badge styling
        $response->assertSee('rounded-full');
    }

    // ========================================================================
    // 1.6 Org show page displays public-blueprint count
    // ========================================================================

    public function test_org_show_displays_public_blueprint_count(): void
    {
        $user = $this->createUserWithPlan();
        $org = $this->createOrganization($user, 'public-count-org', 'Public Count Org');

        // Create 2 public blueprints
        for ($i = 1; $i <= 2; $i++) {
            Blueprint::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $org->id,
                'slug' => 'public-bp-'.$i,
                'title' => 'Public BP '.$i,
                'is_public' => true,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        // Create 3 private blueprints
        for ($i = 1; $i <= 3; $i++) {
            Blueprint::create([
                'uuid' => (string) Str::uuid(),
                'organization_id' => $org->id,
                'slug' => 'private-bp-'.$i,
                'title' => 'Private BP '.$i,
                'is_public' => false,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        $response = $this->actingAs($user)->get(route('organizations.show', $org->slug));

        $response->assertStatus(200);
        $response->assertSee(__('organization.public_blueprints_count'));
        $response->assertSee('2');
    }
}
