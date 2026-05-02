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
}
