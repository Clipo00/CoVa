<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
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
        ?int $categoryId = null,
        array $tabsConfig = [],
        array $variables = [],
    ): Blueprint {
        $plan = $organization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;

        if ($maxBlueprints !== null && $organization->blueprints()->count() >= $maxBlueprints) {
            throw new MaxBlueprintsReachedException($maxBlueprints, $plan->name);
        }

        $blueprint = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $organization->id,
            'category_id' => $categoryId,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            'is_public' => false,
            'tabs_config' => $tabsConfig,
            'created_by' => auth()->id(),
        ]);

        // Crear variables asociadas
        foreach ($variables as $variableData) {
            if (empty($variableData['key'])) {
                continue;
            }

            $blueprint->variables()->create([
                'key' => $variableData['key'],
                'type' => $variableData['type'] ?? 'fixed',
                'default_value' => $variableData['default_value'] ?: null,
                'is_interactive' => $variableData['is_interactive'] ?? false,
                'is_secret' => $variableData['is_secret'] ?? false,
                'section' => $variableData['section'] ?? null,
                'sort_order' => 0,
            ]);
        }

        return $blueprint;
    }
}
