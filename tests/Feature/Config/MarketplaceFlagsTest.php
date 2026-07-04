<?php

declare(strict_types=1);

namespace Tests\Feature\Config;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\PublishBlueprint;
use App\Modules\Blueprint\Actions\VoteBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MarketplaceFlagsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Organization $organization;

    private Blueprint $privateBlueprint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $proPlan = Plan::where('slug', 'pro')->first();
        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization;
        $this->organization = $createOrg->execute($this->owner, 'Test Org', 'test-org');

        // Ensure marketplace org exists (required by PublishBlueprint)
        Organization::firstOrCreate(
            ['slug' => 'covar-marketplace'],
            [
                'name' => 'CoVa Marketplace',
                'owner_id' => $this->owner->id,
                'plan_id' => $proPlan->id,
            ]
        );

        $this->actingAs($this->owner);

        $this->privateBlueprint = Blueprint::create([
            'uuid' => Str::uuid()->toString(),
            'organization_id' => $this->organization->id,
            'slug' => 'test-bp',
            'title' => 'Test Blueprint',
            'tabs_config' => [],
            'is_public' => false,
            'created_by' => $this->owner->id,
        ]);
    }

    private function createPublicBlueprint(): Blueprint
    {
        $bp = Blueprint::create([
            'uuid' => Str::uuid()->toString(),
            'organization_id' => $this->organization->id,
            'slug' => 'public-'.Str::random(6),
            'title' => 'Public Blueprint',
            'tabs_config' => [],
            'is_public' => true,
            'created_by' => $this->owner->id,
        ]);

        return $bp;
    }

    public function test_publish_is_denied_when_marketplace_disabled(): void
    {
        config(['marketplace.enabled' => false]);
        config(['marketplace.billing_enabled' => false]);

        $this->expectException(HttpException::class);

        (new PublishBlueprint)->execute($this->privateBlueprint, $this->owner);
    }

    public function test_publish_succeeds_when_marketplace_enabled(): void
    {
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => false]);

        (new PublishBlueprint)->execute($this->privateBlueprint, $this->owner);

        $this->privateBlueprint->refresh();
        $this->assertTrue($this->privateBlueprint->is_public);
    }

    public function test_publish_with_billing_enabled_checks_plan(): void
    {
        config(['marketplace.enabled' => true]);
        config(['marketplace.billing_enabled' => true]);

        // Owner has pro plan (has_marketplace_publish = true) — should succeed
        (new PublishBlueprint)->execute($this->privateBlueprint, $this->owner);

        $this->privateBlueprint->refresh();
        $this->assertTrue($this->privateBlueprint->is_public);
    }

    public function test_voting_is_denied_when_marketplace_disabled(): void
    {
        $bp = $this->createPublicBlueprint();
        config(['marketplace.enabled' => false]);

        $this->expectException(HttpException::class);

        (new VoteBlueprint)->execute($bp, $this->owner, 1);
    }

    public function test_voting_succeeds_when_marketplace_enabled(): void
    {
        $bp = $this->createPublicBlueprint();
        config(['marketplace.enabled' => true]);

        (new VoteBlueprint)->execute($bp, $this->owner, 1);

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $this->owner->id,
            'blueprint_id' => $bp->id,
            'vote' => 1,
        ]);
    }
}
