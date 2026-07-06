<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

use App\Modules\Blueprint\Enums\TabType;

/**
 * Helper DTO providing accessor methods over resolved TabOutput[].
 *
 * Extracted from BlueprintOutput to be reusable across show page and preview.
 */
final class ResolvedTabs
{
    /**
     * @param  TabOutput[]  $tabs
     */
    public function __construct(
        public readonly array $tabs,
    ) {}

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

        return 'code --install-extension '.implode(' --install-extension ', $extensions);
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
}
