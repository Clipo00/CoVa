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
}
