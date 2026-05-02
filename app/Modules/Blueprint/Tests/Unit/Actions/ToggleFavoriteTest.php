<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Actions\ToggleFavorite;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleFavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_it_adds_to_favorites(): void
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

        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'My BP', 'my-bp');

        $toggle = new ToggleFavorite();
        $result = $toggle->execute($blueprint, $user);

        $this->assertTrue($result);
        $this->assertTrue($blueprint->favoritedBy($user));
    }

    public function test_it_removes_from_favorites(): void
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

        $createBp = new CreateBlueprint();
        $blueprint = $createBp->execute($organization, 'My BP', 'my-bp');

        $toggle = new ToggleFavorite();
        $toggle->execute($blueprint, $user); // Add
        $result = $toggle->execute($blueprint, $user); // Remove

        $this->assertFalse($result);
        $this->assertFalse($blueprint->favoritedBy($user));
    }
}
