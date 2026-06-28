<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Actions\UpdateBlueprint;
use App\Modules\Blueprint\Livewire\Components\VariableManager;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use App\Modules\Shared\ValueObjects\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VariableManagerSortingTest extends TestCase
{
    use RefreshDatabase;

    private array $initialVariables;
    private Plan $plan;
    private User $user;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $this->plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $this->plan->id,
        ]);

        $this->organization = Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $this->organization->members()->attach($this->user->id, ['role' => 'owner']);

        $this->initialVariables = [
            [
                'key' => 'DB_HOST',
                'type' => 'fixed',
                'default_value' => 'localhost',
                'is_interactive' => false,
                'is_secret' => false,
                'section' => null,
            ],
            [
                'key' => 'DB_PORT',
                'type' => 'fixed',
                'default_value' => '3306',
                'is_interactive' => false,
                'is_secret' => false,
                'section' => null,
            ],
            [
                'key' => 'APP_ENV',
                'type' => 'fixed',
                'default_value' => 'production',
                'is_interactive' => true,
                'is_secret' => false,
                'section' => null,
            ],
        ];
    }

    public function test_move_down_swaps_variable_with_next(): void
    {
        $component = Livewire::test(VariableManager::class, [
            'initialVariables' => $this->initialVariables,
        ]);

        $component->call('moveVariable', 0, 1);

        $component->assertSet('variables.0.key', 'DB_PORT');
        $component->assertSet('variables.1.key', 'DB_HOST');
        $component->assertSet('variables.2.key', 'APP_ENV');
    }

    public function test_move_up_swaps_variable_with_previous(): void
    {
        $component = Livewire::test(VariableManager::class, [
            'initialVariables' => $this->initialVariables,
        ]);

        $component->call('moveVariable', 1, -1);

        $component->assertSet('variables.0.key', 'DB_PORT');
        $component->assertSet('variables.1.key', 'DB_HOST');
        $component->assertSet('variables.2.key', 'APP_ENV');
    }

    public function test_move_first_up_does_nothing(): void
    {
        $component = Livewire::test(VariableManager::class, [
            'initialVariables' => $this->initialVariables,
        ]);

        $component->call('moveVariable', 0, -1);

        $component->assertSet('variables.0.key', 'DB_HOST');
        $component->assertSet('variables.1.key', 'DB_PORT');
        $component->assertSet('variables.2.key', 'APP_ENV');
    }

    public function test_move_last_down_does_nothing(): void
    {
        $component = Livewire::test(VariableManager::class, [
            'initialVariables' => $this->initialVariables,
        ]);

        $component->call('moveVariable', 2, 1);

        $component->assertSet('variables.0.key', 'DB_HOST');
        $component->assertSet('variables.1.key', 'DB_PORT');
        $component->assertSet('variables.2.key', 'APP_ENV');
    }

    public function test_move_up_on_middle_element(): void
    {
        $component = Livewire::test(VariableManager::class, [
            'initialVariables' => $this->initialVariables,
        ]);

        $component->call('moveVariable', 2, -1);

        $component->assertSet('variables.0.key', 'DB_HOST');
        $component->assertSet('variables.1.key', 'APP_ENV');
        $component->assertSet('variables.2.key', 'DB_PORT');
    }

    public function test_move_dispatches_updated_event(): void
    {
        $component = Livewire::test(VariableManager::class, [
            'initialVariables' => $this->initialVariables,
        ]);

        $component->call('moveVariable', 0, 1);

        $component->assertDispatched('variables-updated');
    }

    public function test_persist_sort_order_from_array_index(): void
    {
        $this->actingAs($this->user);

        $createBlueprint = new CreateBlueprint();
        $blueprint = $createBlueprint->execute(
            organization: $this->organization,
            title: 'Test BP',
            slug: 'test-bp',
            variables: $this->initialVariables,
        );

        $variables = $blueprint->variables()->orderBy('sort_order')->get();
        $this->assertCount(3, $variables);
        $this->assertEquals('DB_HOST', $variables[0]->key);
        $this->assertEquals(0, $variables[0]->sort_order);
        $this->assertEquals('DB_PORT', $variables[1]->key);
        $this->assertEquals(1, $variables[1]->sort_order);
        $this->assertEquals('APP_ENV', $variables[2]->key);
        $this->assertEquals(2, $variables[2]->sort_order);
    }

    public function test_update_persists_sort_order_from_array_index(): void
    {
        $this->actingAs($this->user);

        $blueprint = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $this->organization->id,
            'slug' => 'test-bp-2',
            'title' => 'Test BP 2',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $this->user->id,
        ]);

        $reorderedVars = [
            ['key' => 'APP_ENV', 'type' => 'fixed', 'default_value' => 'dev'],
            ['key' => 'DB_HOST', 'type' => 'fixed', 'default_value' => 'localhost'],
        ];

        $updateBlueprint = new UpdateBlueprint();
        $updateBlueprint->execute(
            blueprint: $blueprint,
            data: ['title' => 'Updated BP'],
            variables: $reorderedVars,
        );

        $variables = $blueprint->fresh()->variables()->orderBy('sort_order')->get();
        $this->assertCount(2, $variables);
        $this->assertEquals('APP_ENV', $variables[0]->key);
        $this->assertEquals(0, $variables[0]->sort_order);
        $this->assertEquals('DB_HOST', $variables[1]->key);
        $this->assertEquals(1, $variables[1]->sort_order);
    }
}
