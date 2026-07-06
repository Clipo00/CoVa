<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Policies\BlueprintPolicy;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintPolicyTest extends TestCase
{
    use RefreshDatabase;

    private BlueprintPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
        $this->policy = new BlueprintPolicy;
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

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
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

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($developer);
        $createBp = new CreateBlueprint;
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

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
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

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($developer);
        $createBp = new CreateBlueprint;
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

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($developer);
        $createBp = new CreateBlueprint;
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

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org');
        $organization->members()->attach($maintainer->id, ['role' => 'maintainer']);

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Owner BP', 'owner-bp');

        $this->assertFalse($this->policy->delete($maintainer, $blueprint));
    }

    public function test_maintainer_can_delete_own_blueprint(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-del2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $maintainer = User::create([
            'name' => 'Maintainer',
            'email' => 'maintainer-del@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Test Org', 'test-org-mt');
        $organization->members()->attach($maintainer->id, ['role' => 'maintainer']);

        $this->actingAs($maintainer);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Maintainer BP', 'maintainer-bp');

        $this->assertTrue($this->policy->delete($maintainer, $blueprint));
    }

    // --- publish gate tests ---

    public function test_owner_with_paid_plan_can_publish(): void
    {
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Pro Owner',
            'email' => 'pro-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Pro Org', 'pro-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Pro BP', 'pro-bp');

        $this->assertTrue($this->policy->publish($owner, $blueprint));
    }

    public function test_owner_with_free_plan_cannot_publish_when_billing_enabled(): void
    {
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => true]);

        $freePlan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Free Owner',
            'email' => 'free-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Free Org', 'free-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Free BP', 'free-bp');

        $this->assertFalse($this->policy->publish($owner, $blueprint));
    }

    public function test_owner_with_free_plan_cannot_publish_regardless_of_billing(): void
    {
        // Marketplace publish is always plan-gated — free users can never publish
        config(['marketplace.enabled' => true]);

        $freePlan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Free Owner 2',
            'email' => 'free-owner2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Free Org 2', 'free-org-2');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Free BP 2', 'free-bp-2');

        $this->assertFalse($this->policy->publish($owner, $blueprint));
    }

    public function test_non_owner_cannot_publish(): void
    {
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-pub@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $maintainer = User::create([
            'name' => 'Maintainer',
            'email' => 'maintainer-pub@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Pub Org', 'pub-org');
        $organization->members()->attach($maintainer->id, ['role' => 'maintainer']);

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Pub BP', 'pub-bp');

        $this->assertFalse($this->policy->publish($maintainer, $blueprint));
    }

    public function test_non_member_cannot_publish(): void
    {
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-nm@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $nonMember = User::create([
            'name' => 'Non Member',
            'email' => 'non-member-pub@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'NM Org', 'nm-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'NM BP', 'nm-bp');

        $this->assertFalse($this->policy->publish($nonMember, $blueprint));
    }

    // --- vote gate tests ---

    public function test_user_can_vote_on_public_blueprint(): void
    {
        config(['marketplace.enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Owner Vote',
            'email' => 'owner-vote@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Vote Org', 'vote-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Vote BP', 'vote-bp');
        $blueprint->update(['is_public' => true]);

        $member = User::create([
            'name' => 'Member Vote',
            'email' => 'member-vote@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);
        $organization->members()->attach($member->id, ['role' => 'developer']);

        $this->assertTrue($this->policy->vote($member, $blueprint));
    }

    public function test_user_cannot_vote_on_private_blueprint(): void
    {
        config(['marketplace.enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Owner Vote 2',
            'email' => 'owner-vote2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Vote Org 2', 'vote-org-2');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Vote BP 2', 'vote-bp-2');
        // is_public is false by default

        $member = User::create([
            'name' => 'Member Vote 2',
            'email' => 'member-vote2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);
        $organization->members()->attach($member->id, ['role' => 'developer']);

        $this->assertFalse($this->policy->vote($member, $blueprint));
    }

    public function test_non_member_can_vote(): void
    {
        config(['marketplace.enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Owner Vote 3',
            'email' => 'owner-vote3@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Vote Org 3', 'vote-org-3');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Vote BP 3', 'vote-bp-3');
        $blueprint->update(['is_public' => true]);

        $nonMember = User::create([
            'name' => 'Non Member Vote',
            'email' => 'non-member-vote@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $this->assertTrue($this->policy->vote($nonMember, $blueprint));
    }

    public function test_cannot_vote_on_own_blueprint(): void
    {
        config(['marketplace.enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $owner = User::create([
            'name' => 'Owner Vote 4',
            'email' => 'owner-vote4@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($owner, 'Vote Org 4', 'vote-org-4');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($organization, 'Vote BP 4', 'vote-bp-4');
        $blueprint->update(['is_public' => true]);

        // Creator cannot vote on their own blueprint
        $this->assertFalse($this->policy->vote($owner, $blueprint));
    }
}
