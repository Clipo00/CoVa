<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;

class TransferBlueprint
{
    public function execute(Blueprint $blueprint, Organization $targetOrganization, User $user): Blueprint
    {
        // Validar que el user es owner de la org origen
        if (!$user->isOwnerOf($blueprint->organization)) {
            abort(403, __('blueprint.transfer_not_owner'));
        }

        // Validar que el user es owner de la org destino
        if (!$user->isOwnerOf($targetOrganization)) {
            abort(403, __('blueprint.transfer_not_owner_target'));
        }

        // Validar que la org destino es diferente a la origen
        if ($blueprint->organization_id === $targetOrganization->id) {
            abort(422, __('blueprint.transfer_same_org'));
        }

        // Validar que el slug es único en la org destino
        $existingBlueprint = Blueprint::where('organization_id', $targetOrganization->id)
            ->where('slug', $blueprint->slug)
            ->exists();

        if ($existingBlueprint) {
            abort(422, __('blueprint.transfer_slug_exists', ['slug' => $blueprint->slug]));
        }

        // Validar límite de blueprints de la org destino
        $plan = $targetOrganization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;

        if ($maxBlueprints !== null && $targetOrganization->blueprints()->count() >= $maxBlueprints) {
            throw new MaxBlueprintsReachedException($maxBlueprints, $plan->name);
        }

        $blueprint->update(['organization_id' => $targetOrganization->id]);

        return $blueprint->fresh();
    }
}
