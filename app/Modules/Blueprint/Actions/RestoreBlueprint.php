<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Models\Blueprint;

class RestoreBlueprint
{
    public function execute(Blueprint $blueprint): void
    {
        $organization = $blueprint->organization;
        $plan = $organization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;

        // Contar blueprints activos (sin soft deletes) de la organización
        $activeBlueprintsCount = $organization->blueprints()->count();

        if ($maxBlueprints !== null && $activeBlueprintsCount >= $maxBlueprints) {
            throw new MaxBlueprintsReachedException($maxBlueprints, $plan->name);
        }

        $blueprint->restore();
    }
}
