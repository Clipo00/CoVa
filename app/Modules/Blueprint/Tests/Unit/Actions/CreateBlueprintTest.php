<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Exceptions\MaxVariablesReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBlueprintTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_it_creates_blueprint(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($user, 'Test Org', 'test-org');

        $this->actingAs($user);

        $action = new CreateBlueprint();
        $blueprint = $action->execute(
            organization: $organization,
            title: 'My Blueprint',
            slug: 'my-blueprint',
        );

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals('My Blueprint', $blueprint->title);
        $this->assertEquals('my-blueprint', $blueprint->slug);
        $this->assertEquals($organization->id, $blueprint->organization_id);
        $this->assertNotEmpty($blueprint->uuid);
    }

    public function test_it_respects_plan_limit(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($user, 'Test Org', 'test-org');

        $this->actingAs($user);

        $action = new CreateBlueprint();
        
        // Free plan allows 3 blueprints
        $action->execute($organization, 'BP 1', 'bp-1');
        $action->execute($organization, 'BP 2', 'bp-2');
        $action->execute($organization, 'BP 3', 'bp-3');

        $this->expectException(MaxBlueprintsReachedException::class);
        $action->execute($organization, 'BP 4', 'bp-4');
    }

    public function test_it_respects_variable_limit(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $organization = $createOrg->execute($user, 'Test Org', 'test-org');

        $this->actingAs($user);

        $action = new CreateBlueprint();
        
        // Free plan allows 50 variables
        $variables = [];
        for ($i = 1; $i <= 51; $i++) {
            $variables[] = ['key' => "VAR_{$i}", 'type' => 'fixed'];
        }

        $this->expectException(MaxVariablesReachedException::class);
        $action->execute($organization, 'Too Many Vars', 'too-many-vars', variables: $variables);
    }
}
