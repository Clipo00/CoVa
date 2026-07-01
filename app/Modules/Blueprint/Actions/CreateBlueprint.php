<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Exceptions\MaxVariablesReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\ValueObjects\Uuid;

class CreateBlueprint
{
    public function execute(
        Organization $organization,
        string $title,
        string $slug,
        ?string $description = null,
        array $tabsConfig = [],
        array $variables = [],
        array $tagIds = [],
    ): Blueprint {
        $plan = $organization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;

        if ($maxBlueprints !== null && $organization->blueprints()->count() >= $maxBlueprints) {
            throw new MaxBlueprintsReachedException($maxBlueprints, $plan->name);
        }

        $maxVariables = $plan->max_variables_per_blueprint;

        // Count variables + segments (each segment consumes a variable slot)
        $variableCount = count(array_filter($variables, fn ($v) => !empty($v['key'])));
        $segmentCount = $this->countSegmentsInTabs($tabsConfig);
        $totalCount = $variableCount + $segmentCount;

        if ($maxVariables !== null && $totalCount > $maxVariables) {
            throw new MaxVariablesReachedException($maxVariables, $plan->name);
        }

        $blueprint = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $organization->id,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'is_public' => false,
            'tabs_config' => $tabsConfig,
            'created_by' => auth()->id(),
        ]);

        // Crear variables asociadas — sort_order from array index preserves UI order
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

        // Sync tags (limit enforced at form validation level — max 6)
        if (!empty($tagIds)) {
            $blueprint->tags()->sync($tagIds);
        }

        return $blueprint;
    }

    /**
     * Count all segments across all AI Context tabs in the tabs config.
     * Each segment consumes a variable slot from the plan limit.
     *
     * @param  array<int, array{type: string, config: array<string, mixed>}>  $tabsConfig
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
