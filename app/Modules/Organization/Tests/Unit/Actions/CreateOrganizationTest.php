<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Exceptions\MaxOrganizationsReachedException;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrganizationTest extends TestCase
{
    use RefreshDatabase;

    private Plan $freePlan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->freePlan = Plan::where('slug', 'free')->first();
    }

    public function test_it_creates_organization(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->freePlan->id,
        ]);

        $action = new CreateOrganization;
        $organization = $action->execute($user, 'My Org', 'my-org');

        $this->assertInstanceOf(Organization::class, $organization);
        $this->assertEquals('My Org', $organization->name);
        $this->assertEquals('my-org', $organization->slug);
        $this->assertEquals($user->id, $organization->owner_id);
        $this->assertEquals($this->freePlan->id, $organization->plan->id);
    }

    public function test_it_adds_owner_as_member(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->freePlan->id,
        ]);

        $action = new CreateOrganization;
        $organization = $action->execute($user, 'My Org', 'my-org');

        $this->assertTrue($organization->members->contains($user));
        $this->assertEquals('owner', $organization->members->first()->pivot->role);
    }

    public function test_it_respects_plan_limit(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->freePlan->id,
        ]);

        $action = new CreateOrganization;
        $action->execute($user, 'Org 1', 'org-1');
        $action->execute($user, 'Org 2', 'org-2');

        $this->expectException(MaxOrganizationsReachedException::class);
        $action->execute($user, 'Org 3', 'org-3');
    }
}
