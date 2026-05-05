<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Components;

use App\Modules\Blueprint\Enums\TabType;
use Livewire\Component;

/**
 * Livewire component for managing blueprint tabs.
 *
 * Communicates with parent via wire:model on $tabs.
 *
 * Usage:
 * <livewire:blueprint.components.tab-manager wire:model="tabsConfig" />
 */
class TabManager extends Component
{
    /** @var array<int, array{type: string, config: array<string, mixed>}> */
    public array $tabs = [];

    /** @var array<string, string> */
    public array $availableTabTypes = [];

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

        $this->syncToParent();
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

        $this->syncToParent();
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

        $this->syncToParent();
    }

    /**
     * Update extensions for VSCode Extensions tab.
     */
    public function updateVscodeExtensions(int $tabIndex, string $extensionsText): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $extensions = array_values(array_filter(
            array_map('trim', explode("\n", $extensionsText)),
            fn($ext) => $ext !== ''
        ));

        $this->tabs[$tabIndex]['config']['extensions'] = $extensions;

        $this->syncToParent();
    }

    /**
     * Add an empty MCP server entry to a tab.
     */
    public function addMcpServer(int $tabIndex): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['servers'][] = [
            'name' => '',
            'command' => '',
            'args' => [],
        ];

        $this->syncToParent();
    }

    /**
     * Remove an MCP server entry from a tab.
     */
    public function removeMcpServer(int $tabIndex, int $serverIndex): void
    {
        if (!isset($this->tabs[$tabIndex]['config']['servers'][$serverIndex])) {
            return;
        }

        unset($this->tabs[$tabIndex]['config']['servers'][$serverIndex]);
        $this->tabs[$tabIndex]['config']['servers'] = array_values(
            $this->tabs[$tabIndex]['config']['servers']
        );

        $this->syncToParent();
    }

    /**
     * Update an MCP server field.
     */
    public function updateMcpServerField(int $tabIndex, int $serverIndex, string $field, string $value): void
    {
        if (!isset($this->tabs[$tabIndex]['config']['servers'][$serverIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['servers'][$serverIndex][$field] = $value;

        $this->syncToParent();
    }

    /**
     * Toggle a preset for AI Context tab.
     */
    public function togglePreset(int $tabIndex, string $preset): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $presets = $this->tabs[$tabIndex]['config']['presets'] ?? [];

        if (in_array($preset, $presets, true)) {
            $presets = array_values(array_filter($presets, fn($p) => $p !== $preset));
        } else {
            $presets[] = $preset;
        }

        $this->tabs[$tabIndex]['config']['presets'] = $presets;

        $this->syncToParent();
    }

    /**
     * Toggle a skill for AI Context tab.
     */
    public function toggleSkill(int $tabIndex, string $skill): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $skills = $this->tabs[$tabIndex]['config']['skills'] ?? [];

        if (in_array($skill, $skills, true)) {
            $skills = array_values(array_filter($skills, fn($s) => $s !== $skill));
        } else {
            $skills[] = $skill;
        }

        $this->tabs[$tabIndex]['config']['skills'] = $skills;

        $this->syncToParent();
    }

    /**
     * Update custom rules for AI Context tab.
     */
    public function updateCustomRules(int $tabIndex, string $rules): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['custom_rules'] = $rules;

        $this->syncToParent();
    }

    /**
     * Sync state to parent component via Livewire dispatch.
     */
    private function syncToParent(): void
    {
        $this->dispatch('tabs-updated', tabs: $this->tabs);
    }

    public function render()
    {
        return view('blueprint::livewire.components.tab-manager');
    }
}