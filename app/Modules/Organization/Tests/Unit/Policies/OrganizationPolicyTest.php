<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Policies\OrganizationPolicy;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrganizationPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->policy = new OrganizationPolicy();
    }

    private function createUserWithRole(string $role, Organization $organization): User
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization->members()->attach($user->id, ['role' => $role]);

        return $user;
    }

    public function test_owner_can_view(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');

        $this->assertTrue($this->policy->view($owner, $organization));
    }

    public function test_developer_can_view(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');

        $developer = $this->createUserWithRole('developer', $organization);

        $this->assertTrue($this->policy->view($developer, $organization));
    }

    public function test_owner_can_delete(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');

        $this->assertTrue($this->policy->delete($owner, $organization));
    }

    public function test_maintainer_cannot_delete(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');

        $maintainer = $this->createUserWithRole('maintainer', $organization);

        $this->assertFalse($this->policy->delete($maintainer, $organization));
    }

    public function test_owner_can_manage_members(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');

        $this->assertTrue($this->policy->manageMembers($owner, $organization));
    }

    public function test_developer_cannot_manage_members(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');

        $developer = $this->createUserWithRole('developer', $organization);

        $this->assertFalse($this->policy->manageMembers($developer, $organization));
    }
}
