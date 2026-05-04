<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;

class TransferBlueprint
{
    public function execute(Blueprint $blueprint, Organization $targetOrganization, User $user): Blueprint
    {
        // Validar que el user es owner de la org origen
        if (!$user->isOwnerOf($blueprint->organization)) {
            abort(403, 'Solo el owner puede transferir blueprints.');
        }

        // Validar que el user es owner de la org destino
        if (!$user->isOwnerOf($targetOrganization)) {
            abort(403, 'Solo puedes transferir a organizaciones donde eres owner.');
        }

        // Validar que la org destino es diferente a la origen
        if ($blueprint->organization_id === $targetOrganization->id) {
            abort(422, 'No puedes transferir un blueprint a la misma organización.');
        }

        // Validar que el slug es único en la org destino
        $existingBlueprint = Blueprint::where('organization_id', $targetOrganization->id)
            ->where('slug', $blueprint->slug)
            ->exists();

        if ($existingBlueprint) {
            abort(422, "Ya existe un blueprint con el slug '{$blueprint->slug}' en la organización destino. Renombra el blueprint antes de transferir.");
        }

        $blueprint->update(['organization_id' => $targetOrganization->id]);

        return $blueprint->fresh();
    }
}