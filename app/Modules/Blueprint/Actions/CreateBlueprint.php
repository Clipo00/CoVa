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
        array $tabsConfig = []
    ): Blueprint {
        $plan = $organization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;

        if ($maxBlueprints !== null && $organization->blueprints()->count() >= $maxBlueprints) {
            throw new MaxBlueprintsReachedException($maxBlueprints, $plan->name);
        }

        return Blueprint::create([
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
    }
}
