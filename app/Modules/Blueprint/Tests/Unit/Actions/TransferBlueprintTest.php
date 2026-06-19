<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Actions\TransferBlueprint;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class TransferBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private TransferBlueprint $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->action = new TransferBlueprint();
    }

    public function test_transfer_succeeds_when_target_org_under_limit(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $sourceOrg = $createOrg->execute($owner, 'Source Org', 'source-org');
        $targetOrg = $createOrg->execute($owner, 'Target Org', 'target-org');

        $this->actingAs($owner);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($sourceOrg, 'Test BP', 'test-bp');

        $transferred = $this->action->execute($blueprint, $targetOrg, $owner);

        $this->assertEquals($targetOrg->id, $transferred->organization_id);
        $this->assertEquals('test-bp', $transferred->slug);
    }

    public function test_transfer_throws_exception_when_target_org_at_limit(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $sourceOrg = $createOrg->execute($owner, 'Source Org', 'source-org');
        $targetOrg = $createOrg->execute($owner, 'Target Org', 'target-org');

        $this->actingAs($owner);

        // Llenar la org destino hasta el límite (Free plan: max_blueprints_per_org = 3)
        for ($i = 0; $i < $plan->max_blueprints_per_org; $i++) {
            $createBp = new CreateBlueprint();
            $createBp->execute($targetOrg, "BP $i", "bp-$i");
        }

        // Crear un blueprint en la org origen
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($sourceOrg, 'To Transfer', 'to-transfer');

        $this->expectException(MaxBlueprintsReachedException::class);

        $this->action->execute($blueprint, $targetOrg, $owner);
    }

    public function test_transfer_throws_exception_when_actor_not_owner_of_source(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner1 = User::create([
            'name' => 'Owner 1',
            'email' => 'owner1@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $owner2 = User::create([
            'name' => 'Owner 2',
            'email' => 'owner2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $sourceOrg = $createOrg->execute($owner1, 'Source Org', 'source-org');
        $targetOrg = $createOrg->execute($owner2, 'Target Org', 'target-org');

        $this->actingAs($owner1);
        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($sourceOrg, 'Test BP', 'test-bp');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('blueprint.transfer_not_owner'));

        $this->action->execute($blueprint, $targetOrg, $owner2);
    }

    public function test_transfer_throws_exception_when_slug_exists_in_target(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $owner = User::create([
            'name' => 'Owner',
            'email' => 'owner-slug@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $sourceOrg = $createOrg->execute($owner, 'Source Org', 'source-org');
        $targetOrg = $createOrg->execute($owner, 'Target Org', 'target-org');

        $this->actingAs($owner);

        // Create a blueprint in the target org with slug 'test-bp'
        $createBp = new CreateBlueprint();
        $createBp->execute($targetOrg, 'Test BP', 'test-bp');

        // Create another blueprint in the source org with the same slug
        $blueprint = $createBp->execute($sourceOrg, 'Test BP', 'test-bp');

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('blueprint.transfer_slug_exists', ['slug' => 'test-bp']));

        $this->action->execute($blueprint, $targetOrg, $owner);
    }
}
