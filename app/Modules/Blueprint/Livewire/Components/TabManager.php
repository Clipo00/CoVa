<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Components;

use App\Modules\Blueprint\DTOs\TabConfig;
use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Tabs\TabRegistry;
use Livewire\Component;

/**
 * Livewire component for managing blueprint tabs.
 *
 * Usage:
 * <livewire:blueprint.components.tab-manager
 *     :blueprint="$blueprint"
 *     :tabs-config="$existingTabs"
 * />
 *
 * Events emitted:
 * - tabs-updated: when tabs change (payload: array tabs_config)
 */
class TabManager extends Component
{
    /** @var array<int, array{type: string, config: array<string, mixed>}> */
    public array $tabs = [];

    public $blueprint = null;

    /** @var array<string, string> */
    public array $availableTabTypes = [];

    private ?TabRegistry $registry = null;

    public function mount(?array $tabsConfig = null): void
    {
        $this->tabs = $tabsConfig ?? [];
        $this->availableTabTypes = [
            TabType::VSCODE_EXTENSIONS->value => 'VSCode Extensions',
            TabType::MCP_SERVERS->value => 'MCP Servers',
            TabType::AI_CONTEXT->value => 'AI Context',
        ];
    }

    /**
     * Add a new empty tab of the given type.
     */
    public function addTab(string $type): void
    {
        if (!TabType::isValid($type)) {
            return;
        }

        $defaultConfig = match ($type) {
            TabType::VSCODE_EXTENSIONS->value => ['extensions' => []],
            TabType::MCP_SERVERS->value => ['servers' => []],
            TabType::AI_CONTEXT->value => ['presets' => [], 'skills' => [], 'custom_rules' => ''],
            default => [],
        };

        $this->tabs[] = [
            'type' => $type,
            'config' => $defaultConfig,
        ];

        $this->dispatch('tabs-updated', tabs: $this->tabs);
    }

    /**
     * Remove a tab by index.
     */
    public function removeTab(int $index): void
    {
        if (!isset($this->tabs[$index])) {
            return;
        }

        unset($this->tabs[$index]);
        $this->tabs = array_values($this->tabs);

        $this->dispatch('tabs-updated', tabs: $this->tabs);
    }

    /**
     * Move a tab up or down.
     */
    public function moveTab(int $index, int $direction): void
    {
        $newIndex = $index + $direction;

        if ($newIndex < 0 || $newIndex >= count($this->tabs)) {
            return;
        }

        $tabs = $this->tabs;
        $temp = $tabs[$index];
        $tabs[$index] = $tabs[$newIndex];
        $tabs[$newIndex] = $temp;

        $this->tabs = $tabs;

        $this->dispatch('tabs-updated', tabs: $this->tabs);
    }

    /**
     * Update extensions for VSCode Extensions tab.
     *
     * @param string[] $extensions
     */
    public function updateVscodeExtensions(int $tabIndex, array $extensions): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['extensions'] = $extensions;

        $this->dispatch('tabs-updated', tabs: $this->tabs);
    }

    /**
     * Update MCP servers configuration.
     *
     * @param array<int, array{name: string, command: string, args: array<string>}> $servers
     */
    public function updateMcpServers(int $tabIndex, array $servers): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['servers'] = $servers;

        $this->dispatch('tabs-updated', tabs: $this->tabs);
    }

    /**
     * Update AI Context configuration.
     *
     * @param string[] $presets
     * @param string[] $skills
     */
    public function updateAiContext(int $tabIndex, array $presets, array $skills, string $customRules): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['presets'] = $presets;
        $this->tabs[$tabIndex]['config']['skills'] = $skills;
        $this->tabs[$tabIndex]['config']['custom_rules'] = $customRules;

        $this->dispatch('tabs-updated', tabs: $this->tabs);
    }

    /**
     * Get available presets from registry.
     *
     * @return string[]
     */
    public function getAvailablePresets(): array
    {
        return $this->getRegistry()->has('ai_context')
            ? ['psr12', 'solid', 'clean-architecture']
            : [];
    }

    /**
     * Get available skills from registry.
     *
     * @return string[]
     */
    public function getAvailableSkills(): array
    {
        return $this->getRegistry()->has('ai_context')
            ? ['stripe', 'tailwind']
            : [];
    }

    private function getRegistry(): TabRegistry
    {
        if ($this->registry === null) {
            $this->registry = app(TabRegistry::class);
        }

        return $this->registry;
    }

    public function render()
    {
        return view('blueprint::livewire.components.tab-manager');
    }
}
