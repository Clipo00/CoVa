<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tests\Feature\Models;

use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_plan(): void
    {
        $plan = Plan::create([
            'slug' => 'test-plan',
            'name' => 'Test Plan',
            'max_organizations_per_user' => 2,
            'max_blueprints_per_org' => 3,
        ]);

        $this->assertDatabaseHas('plans', [
            'slug' => 'test-plan',
            'name' => 'Test Plan',
        ]);

        $this->assertEquals(2, $plan->max_organizations_per_user);
        $this->assertEquals(3, $plan->max_blueprints_per_org);
    }

    public function test_it_casts_boolean_fields(): void
    {
        $plan = Plan::create([
            'slug' => 'bool-plan',
            'name' => 'Bool Plan',
            'has_api_access' => true,
            'has_marketplace_publish' => false,
        ]);

        $this->assertTrue($plan->has_api_access);
        $this->assertFalse($plan->has_marketplace_publish);
    }

    public function test_it_casts_null_limits_as_unlimited(): void
    {
        $plan = Plan::create([
            'slug' => 'enterprise-test',
            'name' => 'Enterprise Test',
            'max_organizations_per_user' => null,
            'max_blueprints_per_org' => null,
        ]);

        $this->assertNull($plan->max_organizations_per_user);
        $this->assertNull($plan->max_blueprints_per_org);
    }
}
