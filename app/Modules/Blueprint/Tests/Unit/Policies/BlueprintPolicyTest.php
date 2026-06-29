<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Policies\BlueprintPolicy;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintPolicyTest extends TestCase
{
    use RefreshDatabase;

    private BlueprintPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->policy = new BlueprintPolicy();
    }

    public function test_owner_can_update_any_blueprint(): void
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

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Test BP', 'test-bp');

        $this->assertTrue($this->policy->update($owner, $blueprint));
    }

    public function test_creator_can_update_own_blueprint(): void
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
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($developer);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Dev BP', 'dev-bp');

        $this->assertTrue($this->policy->update($developer, $blueprint));
    }

    public function test_developer_cannot_delete_others_blueprint(): void
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
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Owner BP', 'owner-bp');

        $this->assertFalse($this->policy->delete($developer, $blueprint));
    }

    public function test_owner_can_delete_any_blueprint(): void
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
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($developer);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Dev BP', 'dev-bp');

        $this->assertTrue($this->policy->delete($owner, $blueprint));
    }

    public function test_creator_developer_cannot_delete_own_blueprint(): void
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
            'email' => 'dev@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($developer);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Dev BP', 'dev-bp');

        $this->assertFalse($this->policy->delete($developer, $blueprint));
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

        $maintainer = User::create([
            'name' => 'Maintainer',
            'email' => 'maintainer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($maintainer->id, ['role' => 'maintainer']);

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Owner BP', 'owner-bp');

        $this->assertFalse($this->policy->delete($maintainer, $blueprint));
    }

    // ─── Publish method tests (REQ-PUBLISH-2) ───────────────────────────

    public function test_owner_with_pro_plan_can_publish(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Pro Owner',
            'email' => 'pro-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Pro Org', 'pro-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Pro BP', 'pro-bp');

        $this->assertTrue($this->policy->publish($owner, $blueprint));
    }

    public function test_owner_with_free_plan_cannot_publish(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Free Owner',
            'email' => 'free-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Free Org', 'free-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Free BP', 'free-bp');

        $this->assertFalse($this->policy->publish($owner, $blueprint));
    }

    public function test_maintainer_with_pro_plan_can_publish(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Org Owner',
            'email' => 'org-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $maintainer = User::create([
            'name' => 'Pro Maintainer',
            'email' => 'pro-maintainer@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Maint Org', 'maint-org');
        $organization->members()->attach($maintainer->id, ['role' => 'maintainer']);

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Maint BP', 'maint-bp');

        $this->assertTrue($this->policy->publish($maintainer, $blueprint));
    }

    public function test_developer_cannot_publish(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Org Owner',
            'email' => 'org-owner-2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $developer = User::create([
            'name' => 'Developer',
            'email' => 'dev-pub@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'Dev Org', 'dev-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'Dev BP', 'dev-bp');

        $this->assertFalse($this->policy->publish($developer, $blueprint));
    }

    public function test_non_member_cannot_publish(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Org Owner',
            'email' => 'org-owner-3@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $nonMember = User::create([
            'name' => 'Non Member',
            'email' => 'non-member-pub@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($owner, 'NonMem Org', 'nonmem-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'NonMem BP', 'nonmem-bp');

        $this->assertFalse($this->policy->publish($nonMember, $blueprint));
    }
}
