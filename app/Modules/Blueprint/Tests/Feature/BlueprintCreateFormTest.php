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

    // ─── Template Tests (REQ-TEMPLATE-1) ────────────────────────────────

    public function test_selecting_laravel_template_populates_tabs_config(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'template-test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'slug' => 'template-org',
            'name' => 'Template Org',
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
        ]);

        // Select the laravel template
        $component->set('selectedTemplate', 'laravel');

        // tabsConfig should now be populated
        $this->assertNotEmpty($component->get('tabsConfig'));
        // Should contain vscode_extensions tab with laravel extensions
        $tabTypes = array_column($component->get('tabsConfig'), 'type');
        $this->assertContains('vscode_extensions', $tabTypes);
    }

    public function test_not_selecting_template_leaves_tabs_config_empty(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'template-empty@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'slug' => 'template-empty-org',
            'name' => 'Template Empty Org',
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
        ]);

        // Without selecting any template, tabsConfig should remain empty or default
        $this->assertEmpty($component->get('tabsConfig'));
    }

    public function test_selecting_template_sets_ai_context_with_segments_format(): void
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'template-seg@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'slug' => 'template-seg-org',
            'name' => 'Template Seg Org',
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
        ]);

        $component->set('selectedTemplate', 'laravel');

        $tabsConfig = $component->get('tabsConfig');
        $aiTab = collect($tabsConfig)->firstWhere('type', 'ai_context');

        $this->assertNotNull($aiTab, 'AI Context tab should be present in template');
        $this->assertArrayHasKey('segments', $aiTab['config'], 'AI Context config should use segments key');
        $this->assertIsArray($aiTab['config']['segments'], 'segments should be an array');
        $this->assertNotEmpty($aiTab['config']['segments'], 'segments should not be empty');

        // Verify segment structure
        $firstSegment = $aiTab['config']['segments'][0];
        $this->assertArrayHasKey('type', $firstSegment);
        $this->assertArrayHasKey('name', $firstSegment);
        $this->assertArrayHasKey('content', $firstSegment);
        $this->assertContains($firstSegment['type'], ['preset', 'skill']);
    }
}
