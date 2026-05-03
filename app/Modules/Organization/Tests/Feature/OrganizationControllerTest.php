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
}
