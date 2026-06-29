<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

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
     * @param TabOutput[] $tabs
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
}
