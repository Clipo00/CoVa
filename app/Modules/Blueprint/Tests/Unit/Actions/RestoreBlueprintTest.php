<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Actions\RestoreBlueprint;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestoreBlueprintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);
    }

    public function test_it_restores_blueprint(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($user, 'Test Org', 'test-org');

        $blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440010',
            'organization_id' => $organization->id,
            'slug' => 'test-bp',
            'title' => 'Test Blueprint',
            'description' => 'To be restored',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $blueprint->delete();

        $this->assertSoftDeleted($blueprint);

        $action = new RestoreBlueprint;
        $action->execute($blueprint);

        $this->assertDatabaseHas('blueprints', [
            'id' => $blueprint->id,
            'deleted_at' => null,
        ]);
    }

    public function test_it_blocks_restore_when_plan_limit_reached(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $organization = $createOrg->execute($user, 'Test Org', 'test-org');

        // Create 3 active blueprints (free plan limit)
        $this->actingAs($user);
        $createBp = new CreateBlueprint;
        $createBp->execute($organization, 'BP 1', 'bp-1');
        $createBp->execute($organization, 'BP 2', 'bp-2');
        $createBp->execute($organization, 'BP 3', 'bp-3');

        // Create and delete a 4th blueprint
        $blueprintToRestore = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440011',
            'organization_id' => $organization->id,
            'slug' => 'bp-4',
            'title' => 'BP 4',
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);
        $blueprintToRestore->delete();

        $this->expectException(MaxBlueprintsReachedException::class);

        $action = new RestoreBlueprint;
        $action->execute($blueprintToRestore);
    }
}
