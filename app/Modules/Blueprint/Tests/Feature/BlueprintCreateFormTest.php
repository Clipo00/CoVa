<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Livewire\Forms\BlueprintCreateForm;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlueprintCreateFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_submit_rejects_duplicate_tab_types(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $organization->members()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($user);

        $component = Livewire::test(BlueprintCreateForm::class, [
            'userOrganizations' => [
                [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'hasAvailableSlots' => true,
                ],
            ],
            'title' => 'Test Blueprint',
            'slug' => 'test-blueprint',
            'tabsConfig' => [
                ['type' => 'vscode_extensions', 'config' => []],
                ['type' => 'vscode_extensions', 'config' => []],
            ],
        ]);

        $component->call('submit');

        $component->assertHasErrors('tabsConfig');
    }
}
