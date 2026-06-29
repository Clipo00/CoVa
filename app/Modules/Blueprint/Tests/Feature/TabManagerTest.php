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

    // ──────────────────────────────────────────────
    //  AI Context: Segment CRUD
    // ──────────────────────────────────────────────

    public function test_add_preset_segment_appears_with_correct_name(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => ['segments' => []]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('addSegment', 0, 'preset', 'psr12');

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(1, $segments);
        $this->assertEquals('preset', $segments[0]['type']);
        $this->assertEquals('psr12', $segments[0]['name']);
        $this->assertNotNull($segments[0]['content']);
    }

    public function test_add_skill_segment_appears_with_correct_name(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => ['segments' => []]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('addSegment', 0, 'skill', 'stripe');

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(1, $segments);
        $this->assertEquals('skill', $segments[0]['type']);
        $this->assertEquals('stripe', $segments[0]['name']);
        $this->assertNotNull($segments[0]['content']);
    }

    public function test_add_custom_segment_creates_empty_segment(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => ['segments' => []]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('addSegment', 0, 'custom');

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(1, $segments);
        $this->assertEquals('custom', $segments[0]['type']);
        $this->assertEquals('custom-skill', $segments[0]['name']);
        $this->assertNull($segments[0]['content']);
    }

    public function test_add_custom_segment_with_name(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => ['segments' => []]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('addSegment', 0, 'custom', 'My Rules');

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(1, $segments);
        $this->assertEquals('My Rules', $segments[0]['name']);
    }

    public function test_remove_segment_removes_from_array(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'segments' => [
                    ['type' => 'preset', 'name' => 'psr12', 'content' => null],
                    ['type' => 'skill', 'name' => 'stripe', 'content' => null],
                ],
            ]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('removeSegment', 0, 0);

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(1, $segments);
        $this->assertEquals('stripe', $segments[0]['name']);
    }

    public function test_move_segment_up(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'segments' => [
                    ['type' => 'preset', 'name' => 'psr12', 'content' => null],
                    ['type' => 'preset', 'name' => 'solid', 'content' => null],
                ],
            ]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        // Move solid up (index 1 → 0)
        $component->call('moveSegment', 0, 1, -1);

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(2, $segments);
        $this->assertEquals('solid', $segments[0]['name']);
        $this->assertEquals('psr12', $segments[1]['name']);
    }

    public function test_move_segment_down(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'segments' => [
                    ['type' => 'preset', 'name' => 'psr12', 'content' => null],
                    ['type' => 'preset', 'name' => 'solid', 'content' => null],
                ],
            ]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        // Move psr12 down (index 0 → 1)
        $component->call('moveSegment', 0, 0, 1);

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(2, $segments);
        $this->assertEquals('solid', $segments[0]['name']);
        $this->assertEquals('psr12', $segments[1]['name']);
    }

    public function test_update_segment_content(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'segments' => [
                    ['type' => 'custom', 'name' => 'My Rules', 'content' => null],
                ],
            ]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('updateSegmentContent', 0, 0, 'Always use strict types.');

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertEquals('Always use strict types.', $segments[0]['content']);
    }

    public function test_update_segment_name(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'segments' => [
                    ['type' => 'custom', 'name' => 'custom-skill', 'content' => null],
                ],
            ]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('updateSegmentName', 0, 0, 'My Custom Rules');

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertEquals('My Custom Rules', $segments[0]['name']);
    }

    public function test_add_duplicate_segment_name_is_silently_rejected(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'segments' => [
                    ['type' => 'preset', 'name' => 'psr12', 'content' => null],
                ],
            ]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        // Try adding another segment with the same name
        $component->call('addSegment', 0, 'preset', 'psr12');

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(1, $segments);
    }

    public function test_ai_context_default_config_uses_segments(): void
    {
        $component = Livewire::test(TabManager::class, ['tabs' => []]);

        $component->call('addTab', 'ai_context');

        $tabs = $component->get('tabs');
        $this->assertArrayHasKey('segments', $tabs[0]['config']);
        $this->assertEmpty($tabs[0]['config']['segments']);
        $this->assertArrayNotHasKey('presets', $tabs[0]['config']);
        $this->assertArrayNotHasKey('skills', $tabs[0]['config']);
    }

    public function test_segments_are_ordered_by_insertion(): void
    {
        $tabsConfig = [
            ['type' => 'ai_context', 'config' => ['segments' => []]],
        ];

        $component = Livewire::test(TabManager::class, ['tabs' => $tabsConfig]);

        $component->call('addSegment', 0, 'preset', 'solid');
        $component->call('addSegment', 0, 'skill', 'stripe');
        $component->call('addSegment', 0, 'custom', 'My Rules');

        $tabs = $component->get('tabs');
        $segments = $tabs[0]['config']['segments'];

        $this->assertCount(3, $segments);
        $this->assertEquals('solid', $segments[0]['name']);
        $this->assertEquals('stripe', $segments[1]['name']);
        $this->assertEquals('My Rules', $segments[2]['name']);
    }
}
