<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Components;

use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
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

        $this->resolveSegmentContent();
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
            TabType::AI_CONTEXT->value => ['segments' => []],
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
            fn ($ext) => $ext !== ''
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
                fn ($arg) => $arg !== ''
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

    // ──────────────────────────────────────────────
    //  AI Context: Segment CRUD
    // ──────────────────────────────────────────────

    /**
     * Add a segment to an AI Context tab.
     *
     * For preset and skill types, the default content is loaded from the
     * corresponding registry. For custom type, an empty content segment
     * with an editable name is created.
     */
    public function addSegment(int $tabIndex, string $type, string $name = ''): void
    {
        if (!isset($this->tabs[$tabIndex]) || $this->tabs[$tabIndex]['type'] !== 'ai_context') {
            return;
        }

        if ($name === '') {
            $name = $this->suggestSegmentName($type);
        }

        // Prevent duplicate names within segments
        $segments = $this->tabs[$tabIndex]['config']['segments'] ?? [];
        foreach ($segments as $segment) {
            if ($segment['name'] === $name) {
                return; // Silently reject duplicates
            }
        }

        // Load default content from registry for preset/skill
        $content = null;
        if ($type === 'preset') {
            $registry = app()->make('blueprint.presets');
            if ($registry->has($name)) {
                $content = $registry->get($name)->content();
            }
        } elseif ($type === 'skill') {
            $registry = app()->make('blueprint.skills');
            if ($registry->has($name)) {
                $content = $registry->get($name)->content();
            }
        }

        $segments[] = [
            'type' => $type,
            'name' => $name,
            'content' => $content,
        ];

        $this->tabs[$tabIndex]['config']['segments'] = $segments;

        $this->syncToParent();
    }

    /**
     * Remove a segment from an AI Context tab by index.
     */
    public function removeSegment(int $tabIndex, int $segmentIndex): void
    {
        if (!isset($this->tabs[$tabIndex]['config']['segments'][$segmentIndex])) {
            return;
        }

        unset($this->tabs[$tabIndex]['config']['segments'][$segmentIndex]);
        $this->tabs[$tabIndex]['config']['segments'] = array_values(
            $this->tabs[$tabIndex]['config']['segments']
        );

        $this->syncToParent();
    }

    /**
     * Move a segment up or down in the ordered list.
     */
    public function moveSegment(int $tabIndex, int $segmentIndex, int $direction): void
    {
        $segments = $this->tabs[$tabIndex]['config']['segments'] ?? [];
        $newIndex = $segmentIndex + $direction;

        if ($newIndex < 0 || $newIndex >= count($segments)) {
            return;
        }

        $temp = $segments[$segmentIndex];
        $segments[$segmentIndex] = $segments[$newIndex];
        $segments[$newIndex] = $temp;

        $this->tabs[$tabIndex]['config']['segments'] = $segments;

        $this->syncToParent();
    }

    /**
     * Update the content of a segment (override for preset/skill, content for custom).
     */
    public function updateSegmentContent(int $tabIndex, int $segmentIndex, string $content): void
    {
        if (!isset($this->tabs[$tabIndex]['config']['segments'][$segmentIndex])) {
            return;
        }

        $this->tabs[$tabIndex]['config']['segments'][$segmentIndex]['content'] = $content;

        $this->syncToParent();
    }

    /**
     * Update the name of a segment (for custom segments).
     */
    public function updateSegmentName(int $tabIndex, int $segmentIndex, string $name): void
    {
        if (!isset($this->tabs[$tabIndex]['config']['segments'][$segmentIndex])) {
            return;
        }

        if ($name === '') {
            return;
        }

        // Prevent duplicate names within segments (excluding the current one)
        $segments = $this->tabs[$tabIndex]['config']['segments'] ?? [];
        foreach ($segments as $i => $segment) {
            if ($i !== $segmentIndex && $segment['name'] === $name) {
                return; // Silently reject duplicates
            }
        }

        $this->tabs[$tabIndex]['config']['segments'][$segmentIndex]['name'] = $name;

        $this->syncToParent();
    }

    /**
     * Determine the segments that are NOT yet added, for dropdown display.
     *
     * @return string[] Preset names not yet in the current segments
     */
    public function getUnusedPresetsProperty(int $tabIndex): array
    {
        return $this->unusedNames($tabIndex, 'preset', $this->availablePresetNames);
    }

    /**
     * Determine the skills that are NOT yet added, for dropdown display.
     *
     * @return string[] Skill names not yet in the current segments
     */
    public function getUnusedSkillsProperty(int $tabIndex): array
    {
        return $this->unusedNames($tabIndex, 'skill', $this->availableSkillNames);
    }

    /**
     * Suggest a unique name for a new segment.
     */
    private function suggestSegmentName(string $type): string
    {
        return match ($type) {
            'preset' => 'preset',
            'skill' => 'skill',
            'custom' => 'custom-skill',
            default => 'segment',
        };
    }

    /**
     * Filter out names already present in the tab's segments.
     *
     * @param  string  $type  Segment type to filter by
     * @param  string[]  $allNames  All available names
     * @return string[]
     */
    private function unusedNames(int $tabIndex, string $type, array $allNames): array
    {
        if (!isset($this->tabs[$tabIndex]['config']['segments'])) {
            return $allNames;
        }

        $existingSegments = $this->tabs[$tabIndex]['config']['segments'];
        $existingNames = array_map(
            fn (array $s) => $s['name'],
            array_filter($existingSegments, fn (array $s) => $s['type'] === $type),
        );

        return array_values(array_diff($allNames, $existingNames));
    }

    /**
     * Resolve registry content for preset/skill segments that have null content.
     *
     * When tabs are loaded from a template (e.g. on create form), segments only
     * carry their names — not the registry content. This method pre-fills the
     * content from the presets/skills registries so the UI shows actual text.
     */
    private function resolveSegmentContent(): void
    {
        $presets = app()->make('blueprint.presets');
        $skills = app()->make('blueprint.skills');

        foreach ($this->tabs as $tabIndex => &$tab) {
            if (($tab['type'] ?? '') !== 'ai_context') {
                continue;
            }

            $segments = $tab['config']['segments'] ?? [];
            foreach ($segments as $segIndex => &$segment) {
                if ($segment['content'] !== null) {
                    continue; // Already has content (user override)
                }

                if ($segment['type'] === 'preset' && $presets->has($segment['name'])) {
                    $segment['content'] = $presets->get($segment['name'])->content();
                } elseif ($segment['type'] === 'skill' && $skills->has($segment['name'])) {
                    $segment['content'] = $skills->get($segment['name'])->content();
                }
            }
            $tab['config']['segments'] = $segments;
        }
        unset($tab, $segment); // Break references
    }

    /**
     * Sync state to parent component.
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
