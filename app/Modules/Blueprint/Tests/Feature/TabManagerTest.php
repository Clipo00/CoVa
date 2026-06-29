<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Blueprint\Livewire\Components\TabManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TabManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_tab_rejects_duplicate_type(): void
    {
        $tabsConfig = [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => []]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('addTab', 'vscode_extensions');

        $component->assertSet('tabError', __('blueprint.duplicate_tab_type', ['type' => __('blueprint.tab_type_vscode')]));
        $component->assertCount('tabs', 1);
    }

    public function test_add_tab_allows_unique_type(): void
    {
        $tabsConfig = [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => []]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('addTab', 'mcp_servers');

        $component->assertSet('tabError', '');
        $component->assertCount('tabs', 2);
    }

    // --- AI Context: preset toggle via markers ---

    public function test_toggle_preset_on_loads_marked_content(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => ['presets' => [], 'skills' => [], 'custom_rules' => '']],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('togglePreset', 0, 'solid');

        $tabs = $component->get('tabs');
        $rules = $tabs[0]['config']['custom_rules'];

        $this->assertStringContainsString('<!-- BEGIN:preset:solid -->', $rules);
        $this->assertStringContainsString('SOLID Principles', $rules);
        $this->assertStringContainsString('<!-- END:preset:solid -->', $rules);
    }

    public function test_toggle_preset_off_removes_marked_block(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => ['presets' => [], 'skills' => [], 'custom_rules' => '']],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        // Toggle ON: loads content with markers
        $component->call('togglePreset', 0, 'solid');

        // Toggle OFF: removes the marked block
        $component->call('togglePreset', 0, 'solid');

        $tabs = $component->get('tabs');
        $rules = $tabs[0]['config']['custom_rules'];

        $this->assertStringNotContainsString('<!-- BEGIN:preset:solid -->', $rules);
        $this->assertStringNotContainsString('SOLID Principles', $rules);
        $this->assertStringNotContainsString('<!-- END:preset:solid -->', $rules);
        $this->assertEmpty($rules);
    }

    public function test_toggle_one_skill_off_leaves_others_intact(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => ['presets' => [], 'skills' => [], 'custom_rules' => '']],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        // Add two skills
        $component->call('toggleSkill', 0, 'stripe');
        $component->call('toggleSkill', 0, 'tailwind');

        // Remove one
        $component->call('toggleSkill', 0, 'stripe');

        $tabs = $component->get('tabs');
        $rules = $tabs[0]['config']['custom_rules'];

        $this->assertStringNotContainsString('<!-- BEGIN:skill:stripe -->', $rules);
        $this->assertStringContainsString('<!-- BEGIN:skill:tailwind -->', $rules);
        $this->assertStringContainsString('Tailwind CSS', $rules);
    }

    public function test_custom_text_between_blocks_survives_toggle_off(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'presets' => [],
                'skills' => ['stripe'],
                'custom_rules' => "some custom text\n\n<!-- BEGIN:skill:stripe -->\ncontent\n<!-- END:skill:stripe -->",
            ]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        // Toggle off stripe — should remove only the marked block
        $component->call('toggleSkill', 0, 'stripe');

        $tabs = $component->get('tabs');
        $rules = $tabs[0]['config']['custom_rules'];

        $this->assertStringContainsString('some custom text', $rules);
        $this->assertStringNotContainsString('<!-- BEGIN:skill:stripe -->', $rules);
    }
}
