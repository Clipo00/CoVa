<?php

declare(strict_types=1);

namespace App\Modules\Organization\Tests\Unit\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Actions\UpdateOrganizationUserRole;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Policies\OrganizationPolicy;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    public function test_owner_can_update_member_role(): void
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

        $this->assertTrue($this->policy->updateMemberRole($owner, $organization));
    }

    public function test_maintainer_cannot_update_member_role(): void
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

        $this->assertFalse($this->policy->updateMemberRole($maintainer, $organization));
    }

    public function test_owner_self_change_role_is_denied(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'self-change@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org-self');

        // The policy gate passes (owner IS owner), but the action blocks self-change
        $this->assertTrue($this->policy->updateMemberRole($owner, $organization));

        // The action enforces: owner MUST NOT change their own role
        $updateRole = new UpdateOrganizationUserRole();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('organization.cannot_change_owner_role'));

        $updateRole->execute(
            organization: $organization,
            targetUser: $owner,
            newRole: 'maintainer',
            actor: $owner,
        );
    }

    public function test_owner_cannot_update_role_for_non_member(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-nm@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $nonMember = User::create([
            'name' => 'Non Member',
            'email' => 'non-member@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org-nm');

        $updateRole = new UpdateOrganizationUserRole();

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('organization.not_a_member'));

        $updateRole->execute(
            organization: $organization,
            targetUser: $nonMember,
            newRole: 'developer',
            actor: $owner,
        );
    }

    // --- removeMember gate tests ---

    public function test_owner_can_remove_member(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-rm@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org RM', 'test-org-rm');

        $this->assertTrue($this->policy->removeMember($owner, $organization));
    }

    public function test_maintainer_cannot_remove_member(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-rm2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org RM2', 'test-org-rm2');

        $maintainer = $this->createUserWithRole('maintainer', $organization);

        $this->assertFalse($this->policy->removeMember($maintainer, $organization));
    }

    public function test_developer_cannot_remove_member(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-rm3@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org RM3', 'test-org-rm3');

        $developer = $this->createUserWithRole('developer', $organization);

        $this->assertFalse($this->policy->removeMember($developer, $organization));
    }

    public function test_non_member_cannot_remove_member(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-rm4@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org RM4', 'test-org-rm4');

        $nonMember = User::create([
            'name' => 'Non Member',
            'email' => 'non-member-rm@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $this->assertFalse($this->policy->removeMember($nonMember, $organization));
    }
}
