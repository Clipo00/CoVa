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

        $component = Livewire::test(TabManager::class, ['tabsConfig' => $tabsConfig]);

        $component->call('addTab', 'vscode_extensions');

        $component->assertSet('tabError', __('blueprint.duplicate_tab_type', ['type' => 'vscode_extensions']));
        $component->assertCount('tabs', 1);
    }

    public function test_add_tab_allows_unique_type(): void
    {
        $tabsConfig = [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => []]],
        ];

        $component = Livewire::test(TabManager::class, ['tabsConfig' => $tabsConfig]);

        $component->call('addTab', 'mcp_servers');

        $component->assertSet('tabError', '');
        $component->assertCount('tabs', 2);
    }
}
