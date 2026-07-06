<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Models\Blueprint;

/**
 * Complete output after resolving a blueprint's tabs.
 *
 * Delegates tab accessor methods to ResolvedTabs for reuse across
 * show page and preview components.
 */
final class BlueprintOutput
{
    private readonly ResolvedTabs $resolvedTabs;

    /**
     * @param  TabOutput[]  $tabs
     */
    public function __construct(
        public readonly Blueprint $blueprint,
        public readonly array $tabs,
    ) {
        $this->resolvedTabs = new ResolvedTabs($tabs);
    }

    /**
     * Get all tab outputs as array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'blueprint' => [
                'uuid' => $this->blueprint->uuid,
                'title' => $this->blueprint->title,
            ],
            'tabs' => array_map(
                fn (TabOutput $tab) => $tab->toArray(),
                $this->tabs,
            ),
        ];
    }

    /**
     * Get only tab outputs that are arrays (structured data).
     *
     * @return TabOutput[]
     */
    public function getStructuredTabs(): array
    {
        return array_filter(
            $this->tabs,
            fn (TabOutput $tab) => $tab->isArray(),
        );
    }

    public function getAgentMdContent(): ?string
    {
        return $this->resolvedTabs->getAgentMdContent();
    }

    /**
     * @return string[]
     */
    public function getVscodeExtensions(): array
    {
        return $this->resolvedTabs->getVscodeExtensions();
    }

    public function getVscodeInstallCommand(): string
    {
        return $this->resolvedTabs->getVscodeInstallCommand();
    }

    /**
     * @return array<string, mixed>
     */
    public function getMcpServers(): array
    {
        return $this->resolvedTabs->getMcpServers();
    }

    /**
     * Get scripts list.
     *
     * @return array<int, array{command: string, description: string, order: int}>
     */
    public function getScripts(): array
    {
        foreach ($this->tabs as $tab) {
            if ($tab->type === TabType::SCRIPTS && $tab->isArray()) {
                return $tab->content['scripts'] ?? [];
            }
        }

        return [];
    }

    /**
     * Get combined shell script for all scripts.
     */
    public function getScriptsShellScript(): string
    {
        foreach ($this->tabs as $tab) {
            if ($tab->type === TabType::SCRIPTS && $tab->isArray()) {
                return $tab->content['shell_script'] ?? '';
            }
        }

        return '';
    }

    /**
     * Convert the output to an API-friendly array.
     *
     * Returns blueprint metadata, variables with secret masking, and
     * tab-resolved content (agent.md, VSCode extensions, MCP servers, scripts).
     * Secret variable values are replaced with empty strings.
     *
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Blueprint\Models\BlueprintVariable> $variables */
        $variables = $this->blueprint->variables;

        return [
            'uuid' => $this->blueprint->uuid,
            'slug' => $this->blueprint->slug,
            'title' => $this->blueprint->title,
            'description' => $this->blueprint->description,
            'variables' => $variables->map(fn ($v) => [
                'key' => $v->key,
                'type' => $v->type,
                'default_value' => $v->is_secret ? '' : $v->default_value,
                'is_secret' => $v->is_secret,
                'section' => $v->section,
            ])->values()->toArray(),
            'agent_md' => $this->getAgentMdContent(),
            'vscode_extensions' => $this->getVscodeExtensions(),
            'vscode_install_command' => $this->getVscodeInstallCommand(),
            'mcp_servers' => $this->getMcpServers(),
            'scripts' => $this->getScripts(),
            'scripts_shell' => $this->getScriptsShellScript(),
        ];
    }
}
