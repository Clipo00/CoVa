<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Exceptions\MaxVariablesReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Actions\NotifySubscribers;

class UpdateBlueprint
{
    public function execute(
        Blueprint $blueprint,
        array $data,
        array $variables = []
    ): Blueprint {
        // Extraer variables del data si viene ahí
        if (isset($data['variables'])) {
            $variables = $data['variables'];
            unset($data['variables']);
        }

        // Validar límite de variables + segmentos
        $plan = $blueprint->organization->plan;
        $maxVariables = $plan->max_variables_per_blueprint;

        if ($maxVariables !== null) {
            $variableCount = count(array_filter($variables, fn($v) => !empty($v['key'])));
            $tabsConfig = $data['tabs_config'] ?? $blueprint->tabs_config;
            $segmentCount = $this->countSegmentsInTabs($tabsConfig);
            $totalCount = $variableCount + $segmentCount;

            if ($totalCount > $maxVariables) {
                throw new MaxVariablesReachedException($maxVariables, $plan->name);
            }
        }

        $blueprint->update($data);

        // Si hay variables, sincronizarlas — sort_order from array index preserves UI order
        if (!empty($variables)) {
            // Eliminar variables existentes
            $blueprint->variables()->delete();

            // Crear las nuevas
            foreach ($variables as $index => $variableData) {
                if (empty($variableData['key'])) {
                    continue;
                }

                $blueprint->variables()->create([
                    'key' => $variableData['key'],
                    'type' => $variableData['type'] ?? 'fixed',
                    'default_value' => ($variableData['default_value'] ?? null) !== '' ? $variableData['default_value'] : null,
                    'is_interactive' => $variableData['is_interactive'] ?? false,
                    'is_secret' => $variableData['is_secret'] ?? false,
                    'section' => $variableData['section'] ?? null,
                    'section_color' => $variableData['section_color'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }

        // Dispatch notification to subscribers if the blueprint is public
        if ($blueprint->is_public && $blueprint->subscribers_count > 0) {
            NotifySubscribers::dispatch($blueprint->id, 'blueprint_updated');
        }

        return $blueprint->fresh();
    }

    /**
     * Count all segments across all AI Context tabs in the tabs config.
     * Each segment consumes a variable slot from the plan limit.
     *
     * @param array<int, array{type: string, config: array<string, mixed>}> $tabsConfig
     */
    private function countSegmentsInTabs(array $tabsConfig): int
    {
        $count = 0;

        foreach ($tabsConfig as $tab) {
            if (($tab['type'] ?? '') !== 'ai_context') {
                continue;
            }

            $segments = $tab['config']['segments'] ?? [];

            if (!is_array($segments)) {
                continue;
            }

            $count += count($segments);
        }

        return $count;
    }
}