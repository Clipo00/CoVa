<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Livewire\Components\BlueprintPreviewPanel;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BlueprintPreviewPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Preview Test',
            'email' => 'preview@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $this->organization = Organization::create([
            'slug' => 'preview-org',
            'name' => 'Preview Org',
            'owner_id' => $this->user->id,
            'plan_id' => $plan->id,
        ]);

        $this->organization->members()->attach($this->user->id, ['role' => 'owner']);
    }

    public function test_panel_renders_when_tabs_exist(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(BlueprintPreviewPanel::class, [
            'canViewSecrets' => true,
        ]);

        $component->dispatch('preview-refresh', tabsConfig: [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => ['test-ext.vscode']]],
        ]);

        $component->assertSee(__('blueprint.preview_tab_extensions'));
        $component->assertSee('test-ext.vscode');
    }

    public function test_panel_shows_empty_state_when_no_tabs(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(BlueprintPreviewPanel::class, [
            'canViewSecrets' => true,
        ]);

        $component->dispatch('preview-refresh', tabsConfig: []);

        $component->assertDontSee(__('blueprint.preview_tab_extensions'));
        $component->assertDontSee(__('blueprint.agent_context'));
        $component->assertDontSee(__('blueprint.preview_tab_mcp'));
    }

    public function test_panel_updates_when_tabs_change(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(BlueprintPreviewPanel::class, [
            'canViewSecrets' => true,
        ]);

        // First dispatch with one tab
        $component->dispatch('preview-refresh', tabsConfig: [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => ['ext1.vscode']]],
        ]);

        $component->assertSee('ext1.vscode');
        $component->assertDontSee('ext2.vscode');

        // Second dispatch with different tabs
        $component->dispatch('preview-refresh', tabsConfig: [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => ['ext2.vscode']]],
        ]);

        $component->assertSee('ext2.vscode');
        $component->assertDontSee('ext1.vscode');
    }

    public function test_owner_can_see_secret_variables(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(BlueprintPreviewPanel::class, [
            'canViewSecrets' => true,
        ]);

        $component->dispatch('preview-refresh', tabsConfig: [], variables: [
            ['key' => 'SECRET_KEY', 'type' => 'fixed', 'default_value' => 'super-secret-value', 'is_secret' => true],
        ]);

        $component->assertSee('SECRET_KEY');
        $component->assertSee('super-secret-value');
    }

    public function test_non_owner_sees_masked_secrets(): void
    {
        $nonOwner = User::create([
            'name' => 'Non Owner',
            'email' => 'non-owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => Plan::where('slug', 'free')->first()->id,
        ]);

        $this->organization->members()->attach($nonOwner->id, ['role' => 'developer']);

        $this->actingAs($nonOwner);

        $component = Livewire::test(BlueprintPreviewPanel::class, [
            'canViewSecrets' => false,
        ]);

        $component->dispatch('preview-refresh', tabsConfig: [], variables: [
            ['key' => 'SECRET_KEY', 'type' => 'fixed', 'default_value' => 'super-secret-value', 'is_secret' => true],
        ]);

        $component->assertSee('SECRET_KEY');
        $component->assertDontSee('super-secret-value');
    }

    public function test_panel_is_collapsible(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(BlueprintPreviewPanel::class, [
            'canViewSecrets' => true,
        ]);

        $component->dispatch('preview-refresh', tabsConfig: [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => ['ext1.vscode']]],
        ]);

        $component->assertSee('ext1.vscode');
    }
}
