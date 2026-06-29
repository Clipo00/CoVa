<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Components;

use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
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

    /** @var string[] */
    public array $availablePresetNames = [];

    /** @var string[] */
    public array $availableSkillNames = [];

    public string $tabError = '';

    private SegmentRegistry $presetsRegistry;
    private SegmentRegistry $skillsRegistry;

    public function mount(?array $tabs = null): void
    {
        $this->tabs = $tabs ?? [];
        $this->availableTabTypes = [
            TabType::VSCODE_EXTENSIONS->value => __('blueprint.tab_type_vscode'),
            TabType::MCP_SERVERS->value => __('blueprint.tab_type_mcp'),
            TabType::SCRIPTS->value => __('blueprint.tab_type_scripts'),
            TabType::AI_CONTEXT->value => __('blueprint.tab_type_ai'),
        ];

        /** @var AgentGenerator $generator */
        $generator = app()->make(AgentGenerator::class);
        $this->availablePresetNames = $generator->presetNames();
        $this->availableSkillNames = $generator->skillNames();

        // Store registries for content lookup when toggling presets/skills
        $this->presetsRegistry = app()->make('blueprint.presets');
        $this->skillsRegistry = app()->make('blueprint.skills');
    }

    /**
     * Add a new empty tab of the given type.
     */
    public function addTab(string $type): void
    {
        if (!TabType::isValid($type)) {
            return;
        }

        // Validar que no exista ya una pestaña del mismo tipo
        foreach ($this->tabs as $tab) {
            if ($tab['type'] === $type) {
                $this->tabError = __('blueprint.duplicate_tab_type', ['type' => TabType::label($type)]);
                return;
            }
        }

        $this->tabError = '';

        $defaultConfig = match ($type) {
            TabType::VSCODE_EXTENSIONS->value => ['extensions' => []],
            TabType::MCP_SERVERS->value => ['servers' => []],
            TabType::SCRIPTS->value => ['scripts' => []],
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

        // Args field: convert space-separated string to array
        if ($field === 'args') {
            $value = array_values(array_filter(
                array_map('trim', explode(' ', $value)),
                fn($arg) => $arg !== ''
            ));
        }

        $this->tabs[$tabIndex]['config']['servers'][$serverIndex][$field] = $value;

        $this->syncToParent();
    }

    /**
     * Add an empty script entry to a scripts tab.
     */
    public function addScript(int $tabIndex): void
    {
        if (!isset($this->tabs[$tabIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['scripts'][] = [
            'command' => '',
            'description' => '',
        ];

        $this->syncToParent();
    }

    /**
     * Remove a script entry from a scripts tab.
     */
    public function removeScript(int $tabIndex, int $scriptIndex): void
    {
        if (!isset($this->tabs[$tabIndex]['config']['scripts'][$scriptIndex])) {
            return;
        }

        unset($this->tabs[$tabIndex]['config']['scripts'][$scriptIndex]);
        $this->tabs[$tabIndex]['config']['scripts'] = array_values(
            $this->tabs[$tabIndex]['config']['scripts']
        );

        $this->syncToParent();
    }

    /**
     * Update a script field (command or description) in a scripts tab.
     */
    public function updateScriptField(int $tabIndex, int $scriptIndex, string $field, string $value): void
    {
        if (!isset($this->tabs[$tabIndex]['config']['scripts'][$scriptIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['scripts'][$scriptIndex][$field] = $value;

        $this->syncToParent();
    }

    /**
     * Toggle a preset for AI Context tab.
     *
     * When toggled ON, the preset's generated content is loaded into
     * the custom_rules textarea so the user can freely edit it.
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

            // Load preset content into custom_rules for inline editing
            if ($this->presetsRegistry->has($preset)) {
                $presetContent = $this->presetsRegistry->get($preset)->content();
                $currentRules = $this->tabs[$tabIndex]['config']['custom_rules'] ?? '';

                // Only append if not already present (avoid duplicates on re-toggle)
                if ($currentRules === '' || !str_contains($currentRules, trim($presetContent))) {
                    $separator = $currentRules !== '' ? "\n\n" : '';
                    $this->tabs[$tabIndex]['config']['custom_rules'] = $currentRules . $separator . $presetContent;
                }
            }
        }

        $this->tabs[$tabIndex]['config']['presets'] = $presets;

        $this->syncToParent();
    }

    /**
     * Toggle a skill for AI Context tab.
     *
     * When toggled ON, the skill's generated content is loaded into
     * the custom_rules textarea so the user can freely edit it.
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

            // Load skill content into custom_rules for inline editing
            if ($this->skillsRegistry->has($skill)) {
                $skillContent = $this->skillsRegistry->get($skill)->content();
                $currentRules = $this->tabs[$tabIndex]['config']['custom_rules'] ?? '';

                // Only append if not already present (avoid duplicates on re-toggle)
                if ($currentRules === '' || !str_contains($currentRules, trim($skillContent))) {
                    $separator = $currentRules !== '' ? "\n\n" : '';
                    $this->tabs[$tabIndex]['config']['custom_rules'] = $currentRules . $separator . $skillContent;
                }
            }
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
     * Sync state to parent component.
     *
     * In Livewire 3, we use $this->dispatch() with a named event that
     * the parent component listens to via getListeners().
     * The parent (BlueprintEditForm/BlueprintCreateForm) has
     * 'tabs-updated' => 'onTabsUpdated' in its getListeners().
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