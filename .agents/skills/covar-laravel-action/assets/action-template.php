<?php

declare(strict_types=1);

namespace App\Modules\{Module}\Actions;

use App\Modules\{Module}\Exceptions\Max{Reached}Exception;
use App\Modules\{Module}\Models\{Model};
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\ValueObjects\Uuid;

class Create{Model}
{
    public function execute(
        Organization $organization,
        string $title,
        string $slug,
        ?string $description = null,
        // ... otros parámetros específicos
    ): {Model} {
        // 1. Validar límites del plan
        $plan = $organization->plan;
        $maxLimit = $plan->max_{limit}_per_org;

        if ($maxLimit !== null && $organization->{models}()->count() >= $maxLimit) {
            throw new Max{Reached}Exception($maxLimit, $plan->name);
        }

        // 2. Crear el modelo
        ${model} = {Model}::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $organization->id,
            'slug' => $slug,
            'title' => $title,
            'description' => $description,
            // ... otros campos
        ]);

        return ${model};
    }
}