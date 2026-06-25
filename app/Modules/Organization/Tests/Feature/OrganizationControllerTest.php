<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->withoutVite();
    }

    private function createUserWithPlan(): User
    {
        $plan = Plan::where('slug', 'free')->first();
        return User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);
    }

    public function test_index_page_is_accessible_for_authenticated_user(): void
    {
        $user = $this->createUserWithPlan();

        $response = $this->actingAs($user)->get('/organizations');

        $response->assertStatus(200);
        $response->assertSee('Mis Organizaciones');
    }

    public function test_create_page_is_accessible_for_authenticated_user(): void
    {
        $user = $this->createUserWithPlan();

        $response = $this->actingAs($user)->get('/organizations/create');

        $response->assertStatus(200);
        $response->assertSee('Crear tu primera organización');
    }

    public function test_guest_cannot_access_organizations(): void
    {
        $response = $this->get('/organizations');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_create_organization(): void
    {
        $response = $this->get('/organizations/create');

        $response->assertRedirect('/login');
    }

    public function test_owner_can_access_edit_page(): void
    {
        $user = $this->createUserWithPlan();
        $organization = \App\Modules\Organization\Models\Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $user->id,
            'plan_id' => $user->plan_id,
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->get('/organizations/' . $organization->slug . '/edit');

        $response->assertStatus(200);
        $response->assertSee('Editar Organización');
    }

    public function test_owner_can_update_organization(): void
    {
        $user = $this->createUserWithPlan();
        $organization = \App\Modules\Organization\Models\Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $user->id,
            'plan_id' => $user->plan_id,
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)
            ->post('/organizations/' . $organization->slug . '/update', [
                'name' => 'Updated Org',
                'slug' => 'updated-org',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Org',
            'slug' => 'updated-org',
        ]);
    }

    public function test_developer_cannot_access_edit_page(): void
    {
        $owner = $this->createUserWithPlan();
        $organization = \App\Modules\Organization\Models\Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $owner->id,
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $developer = User::create([
            'name' => 'Dev',
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $response = $this->actingAs($developer)->get('/organizations/' . $organization->slug . '/edit');

        $response->assertForbidden();
    }

    public function test_members_page_lists_members(): void
    {
        $user = $this->createUserWithPlan();
        $organization = \App\Modules\Organization\Models\Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $user->id,
            'plan_id' => $user->plan_id,
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->get('/organizations/' . $organization->slug . '/members');

        $response->assertStatus(200);
        $response->assertSee('Miembros');
        $response->assertSee('John Doe');
    }

    public function test_owner_can_invite_member(): void
    {
        $user = $this->createUserWithPlan();
        $organization = \App\Modules\Organization\Models\Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $user->id,
            'plan_id' => $user->plan_id,
        ]);
        $organization->members()->attach($user->id, ['role' => 'owner']);

        $response = $this->actingAs($user)
            ->post('/organizations/' . $organization->slug . '/invite', [
                'email' => 'newdev@example.com',
                'role' => 'developer',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('organization_invitations', [
            'organization_id' => $organization->id,
            'email' => 'newdev@example.com',
            'role' => 'developer',
        ]);
    }

    public function test_developer_cannot_invite_member(): void
    {
        $owner = $this->createUserWithPlan();
        $organization = \App\Modules\Organization\Models\Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $owner->id,
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $developer = User::create([
            'name' => 'Dev',
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $response = $this->actingAs($developer)
            ->post('/organizations/' . $organization->slug . '/invite', [
                'email' => 'newdev@example.com',
                'role' => 'developer',
            ]);

        $response->assertForbidden();
    }

    // --- removeMember tests ---

    public function test_remove_member_requires_auth(): void
    {
        $response = $this->delete('/organizations/test-org/members/1');
        $response->assertRedirect('/login');
    }

    public function test_remove_member_denied_for_non_owner(): void
    {
        $owner = $this->createUserWithPlan();
        $organization = \App\Modules\Organization\Models\Organization::create([
            'slug' => 'test-org-rm',
            'name' => 'Test Org RM',
            'owner_id' => $owner->id,
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $maintainer = User::create([
            'name' => 'Maintainer',
            'email' => 'maintainer-rm@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($maintainer->id, ['role' => 'maintainer']);

        $developer = User::create([
            'name' => 'Dev To Remove',
            'email' => 'dev-rm@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $response = $this->actingAs($maintainer)
            ->delete('/organizations/' . $organization->slug . '/members/' . $developer->id);

        $response->assertForbidden();
    }

    public function test_remove_member_success(): void
    {
        $owner = $this->createUserWithPlan();
        $organization = \App\Modules\Organization\Models\Organization::create([
            'slug' => 'test-org-rm2',
            'name' => 'Test Org RM2',
            'owner_id' => $owner->id,
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($owner->id, ['role' => 'owner']);

        $developer = User::create([
            'name' => 'Dev To Remove',
            'email' => 'dev-rm2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $owner->plan_id,
        ]);
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        // Create a blueprint by the developer to test reassignment
        $blueprint = \App\Modules\Blueprint\Models\Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440030',
            'organization_id' => $organization->id,
            'slug' => 'dev-blueprint',
            'title' => 'Dev Blueprint',
            'tabs_config' => [],
            'created_by' => $developer->id,
        ]);

        $response = $this->actingAs($owner)
            ->delete('/organizations/' . $organization->slug . '/members/' . $developer->id);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify the member was detached
        $this->assertDatabaseMissing('organization_user', [
            'user_id' => $developer->id,
            'organization_id' => $organization->id,
        ]);

        // Verify blueprint was reassigned to owner
        $blueprint->refresh();
        $this->assertEquals($owner->id, $blueprint->created_by);
    }
}
