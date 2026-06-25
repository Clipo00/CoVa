<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Actions\PublishBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PublishBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private PublishBlueprint $action;
    private Organization $organization;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->seed(\Database\Seeders\MarketplaceSeeder::class);

        $this->action = new PublishBlueprint();

        // Enable marketplace and billing for base test setup
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => true]);

        // Create owner on Pro plan (has marketplace publish)
        $proPlan = Plan::where('slug', 'pro')->first();
        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization();
        $this->organization = $createOrg->execute($this->owner, 'Test Org', 'test-org');
    }

    private function createPrivateBlueprint(User $user): Blueprint
    {
        $this->actingAs($user);
        $createBp = new CreateBlueprint();
        return $createBp->execute(
            organization: $this->organization,
            title: 'Test Blueprint',
            slug: 'test-bp',
            description: 'A test blueprint',
        );
    }

    public function test_successful_publish_changes_org_and_is_public(): void
    {
        $blueprint = $this->createPrivateBlueprint($this->owner);
        $marketplaceOrg = Organization::where('slug', 'cova-marketplace')->first();

        $result = $this->action->execute($blueprint, $this->owner);

        $this->assertTrue($result->fresh()->is_public);
        $this->assertEquals($marketplaceOrg->id, $result->fresh()->organization_id);
    }

    public function test_free_plan_denied_when_billing_enabled(): void
    {
        $freePlan = Plan::where('slug', 'free')->first();
        $freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $org = (new CreateOrganization())->execute($freeUser, 'Free Org', 'free-org');
        $this->actingAs($freeUser);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($org, 'Free BP', 'free-bp');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('blueprint.publish_plan_required'));

        $this->action->execute($blueprint, $freeUser);
    }

    public function test_non_owner_allowed_by_action_auth_is_at_policy_level(): void
    {
        // The Action now trusts the caller — authorization is handled
        // at the Policy/Controller level. This test verifies the Action
        // does NOT reject non-owners (the guard is in the Policy).
        $blueprint = $this->createPrivateBlueprint($this->owner);

        // User must be a member of the org to be relevant
        $otherUser = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'plan_id' => Plan::where('slug', 'pro')->first()->id,
        ]);
        $this->organization->members()->attach($otherUser->id, ['role' => 'developer']);

        $marketplaceOrg = Organization::where('slug', 'cova-marketplace')->first();

        $result = $this->action->execute($blueprint, $otherUser);

        // Action still publishes — auth gate is in the Controller/Policy
        $this->assertTrue($result->fresh()->is_public);
        $this->assertEquals($marketplaceOrg->id, $result->fresh()->organization_id);
    }

    public function test_marketplace_disabled_denies(): void
    {
        config(['marketplace.enabled' => false]);

        $blueprint = $this->createPrivateBlueprint($this->owner);

        $this->expectException(HttpException::class);

        $this->action->execute($blueprint, $this->owner);
    }

    public function test_billing_disabled_skips_plan_check(): void
    {
        config(['marketplace.billing_enabled' => false]);

        $freePlan = Plan::where('slug', 'free')->first();
        $freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $org = (new CreateOrganization())->execute($freeUser, 'Free Org 2', 'free-org-2');
        $this->actingAs($freeUser);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($org, 'Free BP 2', 'free-bp-2');

        $marketplaceOrg = Organization::where('slug', 'cova-marketplace')->first();

        // Make user owner of the org
        $org->members()->syncWithoutDetaching([
            $freeUser->id => ['role' => 'owner'],
        ]);

        $result = $this->action->execute($blueprint, $freeUser);

        $this->assertTrue($result->fresh()->is_public);
        $this->assertEquals($marketplaceOrg->id, $result->fresh()->organization_id);
    }
}
