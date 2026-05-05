<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\DTOs\BlueprintOutput;
use App\Modules\Blueprint\DTOs\TabConfig;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Tabs\TabRegistry;

class ResolveBlueprint
{
    public function __construct(
        private readonly TabRegistry $registry,
    ) {}

    /**
     * Resolve a blueprint's tabs into structured output.
     *
     * This action is UI-agnostic and can be used by:
     * - Web controllers for displaying resolved tabs
     * - CLI commands for generating artifacts
     * - API endpoints for programmatic access
     */
    public function execute(Blueprint $blueprint): BlueprintOutput
    {
        $outputs = [];

        $tabsConfig = $blueprint->tabs_config ?? [];

        if (!is_array($tabsConfig)) {
            $tabsConfig = [];
        }

        foreach ($tabsConfig as $tabData) {
            if (!is_array($tabData)) {
                continue;
            }

            try {
                $tabConfig = TabConfig::fromArray($tabData);
            } catch (\InvalidArgumentException) {
                // Skip invalid tab configs
                continue;
            }

            if (!$this->registry->has($tabConfig->type->value)) {
                continue;
            }

            $tab = $this->registry->get($tabConfig->type->value);

            try {
                $outputs[] = $tab->generate($tabConfig->config);
            } catch (\Throwable) {
                // Skip tabs that fail to generate
                continue;
            }
        }

        return new BlueprintOutput($blueprint, $outputs);
    }
}
