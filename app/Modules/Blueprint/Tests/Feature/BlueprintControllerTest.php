<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\MarketplaceSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
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

        $response = $this->actingAs($user)->get('/blueprints?org='.$organization->slug);

        $response->assertStatus(200);
        $response->assertSee('Blueprints');
    }

    public function test_create_page_is_accessible(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $response = $this->actingAs($user)->get('/blueprints/create?org='.$organization->slug);

        $response->assertStatus(200);
        $response->assertSee('Crear Blueprint');
    }

    public function test_create_page_redirects_when_plan_limit_reached(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        // Crear blueprints hasta el límite del plan free (3)
        for ($i = 1; $i <= 3; $i++) {
            Blueprint::create([
                'uuid' => '550e8400-e29b-41d4-a716-4466554400'.$i,
                'organization_id' => $organization->id,
                'slug' => 'bp-'.$i,
                'title' => 'BP '.$i,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        $response = $this->actingAs($user)->get('/blueprints/create?org='.$organization->slug);

        $response->assertRedirect(route('organizations.show', $organization->slug));
        $response->assertSessionHas('error');
    }

    public function test_create_page_without_org_shows_selector(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $response = $this->actingAs($user)->get('/blueprints/create');

        $response->assertStatus(200);
        $response->assertSee('Organización');
        $response->assertSee($organization->name);
    }

    public function test_create_page_redirects_when_no_orgs_available(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        // Llenar la org hasta el límite
        for ($i = 1; $i <= 3; $i++) {
            Blueprint::create([
                'uuid' => '550e8400-e29b-41d4-a716-4466554400'.$i,
                'organization_id' => $organization->id,
                'slug' => 'bp-'.$i,
                'title' => 'BP '.$i,
                'tabs_config' => [],
                'created_by' => $user->id,
            ]);
        }

        // Acceder sin parámetro de org
        $response = $this->actingAs($user)->get('/blueprints/create');

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
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

        $response = $this->actingAs($user)->get('/b/'.$blueprint->slug);

        $response->assertStatus(200);
        $response->assertSee('Test Blueprint');
    }

    public function test_show_page_has_download_elements(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655449000',
            'organization_id' => $organization->id,
            'slug' => 'download-test',
            'title' => 'Download Test Blueprint',
            'description' => 'Blueprint with download elements',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                            ['type' => 'skill', 'name' => 'stripe'],
                        ],
                    ],
                ],
            ],
            'created_by' => $user->id,
        ]);

        // Create variables for .env download
        BlueprintVariable::create([
            'blueprint_id' => $blueprint->id,
            'key' => 'APP_NAME',
            'type' => 'fixed',
            'default_value' => 'MyApp',
            'is_secret' => false,
            'section' => 'app',
            'sort_order' => 0,
        ]);
        BlueprintVariable::create([
            'blueprint_id' => $blueprint->id,
            'key' => 'API_KEY',
            'type' => 'fixed',
            'default_value' => 'should-not-appear',
            'is_secret' => true,
            'section' => 'secrets',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->get('/b/'.$blueprint->slug);

        $response->assertStatus(200);

        // Vault fetch card
        $response->assertSee('cova vault:fetch download-test');

        // Download agent.md button
        $response->assertSee(__('blueprint.download_agent_md'));

        // .env download button
        $response->assertSee(__('blueprint.download_env'));

        // Vault fetch label
        $response->assertSee(__('blueprint.vault_fetch_label'));

        // Individual segment panels (psr12, stripe) are rendered
        $response->assertSee('psr12');
        $response->assertSee('stripe');
        $response->assertSee(__('blueprint.download_segment'));
    }

    public function test_edit_page_is_accessible_to_authorized_user(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440006',
            'organization_id' => $organization->id,
            'slug' => 'edit-me',
            'title' => 'Edit Me',
            'description' => 'To be edited',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/b/'.$blueprint->slug.'/edit');

        $response->assertStatus(200);
        $response->assertSee('Editar Blueprint');
        $response->assertSee('Edit Me');
    }

    public function test_owner_can_delete_blueprint(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
            'organization_id' => $organization->id,
            'slug' => 'delete-me',
            'title' => 'Delete Me',
            'description' => 'To be deleted',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post('/blueprints/'.$blueprint->uuid.'/delete');

        $response->assertRedirect();
        $this->assertSoftDeleted($blueprint);
    }

    public function test_non_owner_cannot_delete_blueprint(): void
    {
        [$owner, $organization] = $this->createUserWithOrg();

        $developer = User::create([
            'name' => 'Dev',
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $organization->plan_id,
        ]);
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440002',
            'organization_id' => $organization->id,
            'slug' => 'no-delete',
            'title' => 'No Delete',
            'description' => 'Cannot delete',
            'tabs_config' => [],
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($developer)
            ->post('/blueprints/'.$blueprint->uuid.'/delete');

        $response->assertForbidden();
        $this->assertDatabaseHas('blueprints', ['uuid' => $blueprint->uuid, 'deleted_at' => null]);
    }

    public function test_deleted_page_shows_trashed_blueprints(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440003',
            'organization_id' => $organization->id,
            'slug' => 'trashed',
            'title' => 'Trashed Blueprint',
            'description' => 'Deleted blueprint',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $blueprint->delete();

        $response = $this->actingAs($user)->get('/blueprints/deleted');

        $response->assertStatus(200);
        $response->assertSee('Trashed Blueprint');
    }

    public function test_owner_can_restore_blueprint(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440004',
            'organization_id' => $organization->id,
            'slug' => 'restore-me',
            'title' => 'Restore Me',
            'description' => 'To be restored',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $blueprint->delete();

        $response = $this->actingAs($user)
            ->post('/blueprints/'.$blueprint->uuid.'/restore');

        $response->assertRedirect();
        $this->assertDatabaseHas('blueprints', [
            'uuid' => $blueprint->uuid,
            'deleted_at' => null,
        ]);
    }

    public function test_non_owner_cannot_restore_blueprint(): void
    {
        [$owner, $organization] = $this->createUserWithOrg();

        $maintainer = User::create([
            'name' => 'Maintainer',
            'email' => 'maintainer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $organization->plan_id,
        ]);
        $organization->members()->attach($maintainer->id, ['role' => 'maintainer']);

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440005',
            'organization_id' => $organization->id,
            'slug' => 'no-restore',
            'title' => 'No Restore',
            'description' => 'Cannot restore',
            'tabs_config' => [],
            'created_by' => $owner->id,
        ]);
        $blueprint->delete();

        $response = $this->actingAs($maintainer)
            ->post('/blueprints/'.$blueprint->uuid.'/restore');

        $response->assertForbidden();
        $this->assertSoftDeleted($blueprint);
    }

    // --- publish tests ---

    public function test_publish_requires_auth(): void
    {
        $response = $this->post('/blueprints/some-uuid/publish');
        $response->assertRedirect('/login');
    }

    public function test_publish_without_permission_denied(): void
    {
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => true]);

        $this->seed(MarketplaceSeeder::class);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'pub-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $org = Organization::create([
            'slug' => 'pub-org',
            'name' => 'Pub Org',
            'owner_id' => $owner->id,
            'plan_id' => $proPlan->id,
        ]);
        $org->members()->attach($owner->id, ['role' => 'owner']);

        $maintainer = User::create([
            'name' => 'Maintainer',
            'email' => 'pub-maintainer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);
        $org->members()->attach($maintainer->id, ['role' => 'maintainer']);

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440011',
            'organization_id' => $org->id,
            'slug' => 'publish-deny',
            'title' => 'Publish Deny Test',
            'tabs_config' => [],
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($maintainer)
            ->post('/blueprints/'.$blueprint->uuid.'/publish');

        $response->assertForbidden();
    }

    public function test_publish_successful(): void
    {
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => true]);

        $this->seed(MarketplaceSeeder::class);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Pub Owner',
            'email' => 'pub-success@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $org = Organization::create([
            'slug' => 'pub-success-org',
            'name' => 'Pub Success Org',
            'owner_id' => $owner->id,
            'plan_id' => $proPlan->id,
        ]);
        $org->members()->attach($owner->id, ['role' => 'owner']);

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440012',
            'organization_id' => $org->id,
            'slug' => 'publish-success',
            'title' => 'Publish Success Test',
            'tabs_config' => [],
            'created_by' => $owner->id,
        ]);

        $marketplaceOrg = Organization::where('slug', 'cova-marketplace')->first();

        $response = $this->actingAs($owner)
            ->post('/blueprints/'.$blueprint->uuid.'/publish');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $blueprint->refresh();
        $this->assertTrue($blueprint->is_public);
        // Original stays in user's org — a copy was created in marketplace
        $this->assertEquals($org->id, $blueprint->organization_id);

        // Verify marketplace copy exists
        $marketplaceOrg = Organization::where('slug', 'cova-marketplace')->first();
        $this->assertDatabaseHas('blueprints', [
            'organization_id' => $marketplaceOrg->id,
            'is_public' => true,
        ]);
    }

    // --- vote tests ---

    public function test_vote_requires_auth(): void
    {
        $response = $this->post('/blueprints/some-uuid/vote', ['vote_type' => 'up']);
        $response->assertRedirect('/login');
    }

    public function test_vote_success(): void
    {
        config(['marketplace.enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Vote Owner',
            'email' => 'vote-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $org = Organization::create([
            'slug' => 'vote-org',
            'name' => 'Vote Org',
            'owner_id' => $owner->id,
            'plan_id' => $proPlan->id,
        ]);
        $org->members()->attach($owner->id, ['role' => 'owner']);

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440021',
            'organization_id' => $org->id,
            'slug' => 'vote-test',
            'title' => 'Vote Test',
            'tabs_config' => [],
            'is_public' => true,
            'created_by' => $owner->id,
        ]);

        // Create a different user to vote (cannot self-vote)
        $voter = User::create([
            'name' => 'Voter',
            'email' => 'voter@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $response = $this->actingAs($voter)
            ->post('/blueprints/'.$blueprint->uuid.'/vote', ['vote_type' => 'up']);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $voter->id,
            'blueprint_id' => $blueprint->id,
            'vote' => 1,
        ]);
    }

    // --- friendly URLs tests ---

    public function test_slug_show_page_resolves_blueprint(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655449999',
            'organization_id' => $organization->id,
            'slug' => 'test-bp-slug',
            'title' => 'Slug Test',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/b/test-bp-slug');

        $response->assertStatus(200);
        $response->assertSee('Slug Test');
    }

    public function test_slug_edit_page_is_accessible(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655449998',
            'organization_id' => $organization->id,
            'slug' => 'edit-via-slug',
            'title' => 'Edit Via Slug',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/b/edit-via-slug/edit');

        $response->assertStatus(200);
        $response->assertSee('Editar Blueprint');
    }

    public function test_legacy_uuid_show_redirects_to_slug(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655449997',
            'organization_id' => $organization->id,
            'slug' => 'legacy-redirect',
            'title' => 'Legacy Redirect',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/blueprints/550e8400-e29b-41d4-a716-446655449997');

        $response->assertStatus(301);
        $response->assertRedirect('/b/legacy-redirect');
    }

    public function test_legacy_uuid_edit_redirects_to_slug_edit(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655449996',
            'organization_id' => $organization->id,
            'slug' => 'legacy-edit-redirect',
            'title' => 'Legacy Edit Redirect',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/blueprints/550e8400-e29b-41d4-a716-446655449996/edit');

        $response->assertStatus(301);
        $response->assertRedirect('/b/legacy-edit-redirect/edit');
    }

    public function test_invalid_slug_uppercase_returns_404(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $response = $this->actingAs($user)->get('/b/MyBlueprint');

        $response->assertStatus(404);
    }

    public function test_invalid_slug_underscore_returns_404(): void
    {
        [$user, $organization] = $this->createUserWithOrg();

        $response = $this->actingAs($user)->get('/b/my_blueprint');

        $response->assertStatus(404);
    }

    public function test_vote_throttle_exceeded(): void
    {
        config(['marketplace.enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Throttle Owner',
            'email' => 'throttle-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $org = Organization::create([
            'slug' => 'throttle-org',
            'name' => 'Throttle Org',
            'owner_id' => $owner->id,
            'plan_id' => $proPlan->id,
        ]);
        $org->members()->attach($owner->id, ['role' => 'owner']);

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440022',
            'organization_id' => $org->id,
            'slug' => 'throttle-test',
            'title' => 'Throttle Test',
            'tabs_config' => [],
            'is_public' => true,
            'created_by' => $owner->id,
        ]);

        // Exceed the throttle limit (10 requests per minute)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->actingAs($owner)
                ->post('/blueprints/'.$blueprint->uuid.'/vote', ['vote_type' => 'up']);
        }

        $response->assertStatus(429);
    }
}
