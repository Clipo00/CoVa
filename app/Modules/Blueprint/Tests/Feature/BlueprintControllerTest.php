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

        $response = $this->actingAs($user)->get('/blueprints/' . $blueprint->uuid . '/edit');

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
            ->post('/blueprints/' . $blueprint->uuid . '/delete');

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
            ->post('/blueprints/' . $blueprint->uuid . '/delete');

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
            ->post('/blueprints/' . $blueprint->uuid . '/restore');

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
            ->post('/blueprints/' . $blueprint->uuid . '/restore');

        $response->assertForbidden();
        $this->assertSoftDeleted($blueprint);
    }
}
