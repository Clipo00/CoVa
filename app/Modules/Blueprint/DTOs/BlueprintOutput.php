<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Models\Blueprint;

/**
 * Complete output after resolving a blueprint's tabs.
 */
final class BlueprintOutput
{
    /**
     * @param TabOutput[] $tabs
     */
    public function __construct(
        public readonly Blueprint $blueprint,
        public readonly array $tabs,
    ) {}

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
                fn(TabOutput $tab) => $tab->toArray(),
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
            fn(TabOutput $tab) => $tab->isArray(),
        );
    }

    /**
     * Get agent.md content if AI Context tab was processed.
     */
    public function getAgentMdContent(): ?string
    {
        foreach ($this->tabs as $tab) {
            if ($tab->type === TabType::AI_CONTEXT && $tab->isMarkdown()) {
                return $tab->content;
            }
        }

        return null;
    }

    /**
     * Get VSCode extensions list.
     *
     * @return string[]
     */
    public function getVscodeExtensions(): array
    {
        foreach ($this->tabs as $tab) {
            if ($tab->type === TabType::VSCODE_EXTENSIONS && $tab->isArray()) {
                return $tab->content['extensions'] ?? [];
            }
        }

        return [];
    }

    /**
     * Get VSCode extensions install command.
     */
    public function getVscodeInstallCommand(): string
    {
        $extensions = $this->getVscodeExtensions();

        if (empty($extensions)) {
            return '';
        }

        return 'code --install-extension ' . implode(' --install-extension ', $extensions);
    }

    /**
     * Get MCP servers configuration.
     *
     * @return array<string, mixed>
     */
    public function getMcpServers(): array
    {
        foreach ($this->tabs as $tab) {
            if ($tab->type === TabType::MCP_SERVERS && $tab->isArray()) {
                return $tab->content;
            }
        }

        return [];
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
}
