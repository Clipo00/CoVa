<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBlueprintWithVariablesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_it_creates_blueprint_with_variables(): void
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
        $variables = [
            [
                'key' => 'DB_HOST',
                'type' => 'fixed',
                'default_value' => 'localhost',
                'is_interactive' => false,
                'is_secret' => false,
            ],
            [
                'key' => 'API_KEY',
                'type' => 'empty',
                'default_value' => '',
                'is_interactive' => true,
                'is_secret' => true,
            ],
        ];

        $blueprint = $action->execute(
            organization: $organization,
            title: 'My Blueprint',
            slug: 'my-blueprint',
            variables: $variables,
        );

        $this->assertInstanceOf(Blueprint::class, $blueprint);
        $this->assertEquals(2, $blueprint->variables()->count());
        
        $dbHost = $blueprint->variables()->where('key', 'DB_HOST')->first();
        $this->assertNotNull($dbHost);
        $this->assertEquals('fixed', $dbHost->type);
        $this->assertEquals('localhost', $dbHost->default_value);
        $this->assertFalse($dbHost->is_interactive);
        $this->assertFalse($dbHost->is_secret);

        $apiKey = $blueprint->variables()->where('key', 'API_KEY')->first();
        $this->assertNotNull($apiKey);
        $this->assertEquals('empty', $apiKey->type);
        $this->assertNull($apiKey->default_value);
        $this->assertTrue($apiKey->is_interactive);
        $this->assertTrue($apiKey->is_secret);
    }

    public function test_it_skips_empty_keys(): void
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
        $variables = [
            [
                'key' => 'VALID_KEY',
                'type' => 'fixed',
                'default_value' => 'value',
            ],
            [
                'key' => '',
                'type' => 'fixed',
                'default_value' => '',
            ],
        ];

        $blueprint = $action->execute(
            organization: $organization,
            title: 'Test BP',
            slug: 'test-bp',
            variables: $variables,
        );

        $this->assertEquals(1, $blueprint->variables()->count());
        $this->assertEquals('VALID_KEY', $blueprint->variables()->first()->key);
    }
}
