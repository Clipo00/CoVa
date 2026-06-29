<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Unit\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Policies\MarketplacePolicy;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use App\Modules\Shared\ValueObjects\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplacePolicyTest extends TestCase
{
    use RefreshDatabase;

    private MarketplacePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $this->policy = new MarketplacePolicy();
    }

    public function test_user_with_org_can_subscribe(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Policy Test',
            'email' => 'policy-test-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $org = $createOrg->execute($user, 'Policy Org', 'policy-org');

        $blueprint = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $org->id,
            'slug' => 'dummy-bp',
            'title' => 'Dummy',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $this->assertTrue($this->policy->subscribe($user, $blueprint));
    }

    public function test_user_without_org_cannot_subscribe(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'No Org User',
            'email' => 'no-org-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        // Create an org as a different user so this user has no org
        $otherUser = User::create([
            'name' => 'Other',
            'email' => 'other-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);
        $createOrg = new CreateOrganization();
        $org = $createOrg->execute($otherUser, 'Other Org', 'other-org');

        $blueprint = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $org->id,
            'slug' => 'dummy-bp',
            'title' => 'Dummy',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $otherUser->id,
        ]);

        $this->assertFalse($this->policy->subscribe($user, $blueprint));
    }
}
