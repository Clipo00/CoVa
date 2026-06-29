<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\DTOs\TabConfig;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Tabs\TabRegistry;
use Illuminate\Support\Facades\Log;

/**
 * Resolve blueprint tabs from an in-memory config array, without a Blueprint model.
 *
 * Mirrors ResolveBlueprint logic but accepts raw tabsConfig data.
 * Used by the live preview component to resolve draft tabs before persistence.
 */
class ResolveBlueprintPreview
{
    public function __construct(
        private readonly TabRegistry $registry,
    ) {}

    /**
     * Resolve tabs from a raw config array.
     *
     * @param array<int, array{type: string, config: array<string, mixed>}> $tabsConfig
     * @return TabOutput[]
     */
    public function execute(array $tabsConfig): array
    {
        $outputs = [];

        foreach ($tabsConfig as $tabData) {
            if (!is_array($tabData)) {
                continue;
            }

            try {
                $tabConfig = TabConfig::fromArray($tabData);
            } catch (\InvalidArgumentException | \TypeError $e) {
                Log::warning('Invalid tab config in preview resolution', [
                    'tab_data' => $tabData,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            if (!$this->registry->has($tabConfig->type->value)) {
                Log::warning('Unknown tab type in preview resolution', [
                    'tab_type' => $tabConfig->type->value,
                ]);
                continue;
            }

            $tab = $this->registry->get($tabConfig->type->value);

            try {
                $outputs[] = $tab->generate($tabConfig->config);
            } catch (\Throwable $e) {
                Log::error('Failed to generate tab output in preview', [
                    'tab_type' => $tabConfig->type->value,
                    'config' => $tabConfig->config,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                continue;
            }
        }

        return $outputs;
    }
}
