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
use Database\Seeders\MarketplaceSeeder;
use Database\Seeders\PlanSeeder;
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

        $this->seed(PlanSeeder::class);
        $this->seed(MarketplaceSeeder::class);

        $this->action = new PublishBlueprint;

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

        $createOrg = new CreateOrganization;
        $this->organization = $createOrg->execute($this->owner, 'Test Org', 'test-org');
    }

    private function createPrivateBlueprint(User $user): Blueprint
    {
        $this->actingAs($user);
        $createBp = new CreateBlueprint;

        return $createBp->execute(
            organization: $this->organization,
            title: 'Test Blueprint',
            slug: 'test-bp',
            description: 'A test blueprint',
        );
    }

    public function test_successful_publish_creates_copy_and_marks_original_public(): void
    {
        $blueprint = $this->createPrivateBlueprint($this->owner);
        $originalOrgId = $blueprint->organization_id;

        // Execute publish — returns the original blueprint
        $result = $this->action->execute($blueprint, $this->owner);

        // Original stays in user's org and is now public
        $this->assertTrue($result->fresh()->is_public);
        $this->assertEquals($originalOrgId, $result->fresh()->organization_id);

        // A marketplace copy was created
        $marketplaceOrg = Organization::where('slug', 'covar-marketplace')->first();
        $this->assertDatabaseHas('blueprints', [
            'slug' => 'test-bp',
            'organization_id' => $marketplaceOrg->id,
            'is_public' => true,
        ]);

        // A subscription record was created for the creator
        $marketplaceCopy = Blueprint::where('organization_id', $marketplaceOrg->id)->where('slug', 'test-bp')->first();
        $this->assertDatabaseHas('blueprint_subscriptions', [
            'user_id' => $this->owner->id,
            'subscribed_blueprint_id' => $marketplaceCopy->id,
            'copied_blueprint_id' => $result->id,
        ]);
    }

    public function test_free_plan_is_denied(): void
    {
        // Free plan users can never publish — plan check is always enforced
        $freePlan = Plan::where('slug', 'free')->first();
        $freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $org = (new CreateOrganization)->execute($freeUser, 'Free Org', 'free-org');
        $this->actingAs($freeUser);
        $createBp = new CreateBlueprint;
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
        $originalOrgId = $blueprint->organization_id;

        // User must be a member of the org to be relevant
        $otherUser = User::create([
            'name' => 'Other',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'plan_id' => Plan::where('slug', 'pro')->first()->id,
        ]);
        $this->organization->members()->attach($otherUser->id, ['role' => 'developer']);

        $result = $this->action->execute($blueprint, $otherUser);

        // Action still publishes — auth gate is in the Controller/Policy
        // Original stays in its org
        $this->assertTrue($result->fresh()->is_public);
        $this->assertEquals($originalOrgId, $result->fresh()->organization_id);
    }

    public function test_marketplace_disabled_denies(): void
    {
        config(['marketplace.enabled' => false]);

        $blueprint = $this->createPrivateBlueprint($this->owner);

        $this->expectException(HttpException::class);

        $this->action->execute($blueprint, $this->owner);
    }

    public function test_free_plan_denied_regardless_of_billing(): void
    {
        // Even with billing disabled, free plan users cannot publish
        config(['marketplace.billing_enabled' => false]);

        $freePlan = Plan::where('slug', 'free')->first();
        $freeUser = User::create([
            'name' => 'Free User',
            'email' => 'free2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $freePlan->id,
        ]);

        $org = (new CreateOrganization)->execute($freeUser, 'Free Org 2', 'free-org-2');
        $this->actingAs($freeUser);
        $createBp = new CreateBlueprint;
        $blueprint = $createBp->execute($org, 'Free BP 2', 'free-bp-2');

        // Make user owner of the org
        $org->members()->syncWithoutDetaching([
            $freeUser->id => ['role' => 'owner'],
        ]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('blueprint.publish_plan_required'));

        $this->action->execute($blueprint, $freeUser);
    }
}
