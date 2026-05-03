<?php

declare(strict_types=1);

namespace App\Modules\Auth\Tests\Unit\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_user_can_check_role_in_organization(): void
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

        $this->assertTrue($owner->hasRoleInOrganization($organization, 'owner'));
        $this->assertTrue($owner->hasRoleInOrganization($organization, ['owner', 'maintainer']));
        $this->assertFalse($owner->hasRoleInOrganization($organization, 'developer'));
    }

    public function test_user_can_check_if_is_owner(): void
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

        $this->assertTrue($owner->isOwnerOf($organization));
    }

    public function test_can_manage_members(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $maintainer = User::create([
            'name' => 'Maintainer',
            'email' => 'maintainer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($maintainer->id, ['role' => 'maintainer']);
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->assertTrue($owner->canManageMembers($organization));
        $this->assertTrue($maintainer->canManageMembers($organization));
        $this->assertFalse($developer->canManageMembers($organization));
    }

    public function test_can_create_blueprints(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->assertTrue($owner->canCreateBlueprints($organization));
        $this->assertTrue($developer->canCreateBlueprints($organization));
    }
}
