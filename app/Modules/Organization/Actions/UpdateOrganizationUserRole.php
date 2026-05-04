<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;

class UpdateOrganizationUserRole
{
    public function execute(
        Organization $organization,
        User $targetUser,
        string $newRole,
        User $actor
    ): void {
        // El actor debe poder gestionar miembros
        if (!$actor->canManageMembers($organization)) {
            abort(403, 'No tienes permisos para gestionar miembros.');
        }

        // No se puede cambiar el rol del owner
        if ($targetUser->id === $organization->owner_id) {
            abort(403, 'No puedes cambiar el rol del propietario de la organización.');
        }

        // Validar que el nuevo rol es válido
        if (!in_array($newRole, ['developer', 'maintainer'])) {
            abort(422, 'El rol debe ser developer o maintainer.');
        }

        // Verificar que el usuario es miembro de la organización
        if (!$organization->members()->where('user_id', $targetUser->id)->exists()) {
            abort(422, 'El usuario no es miembro de esta organización.');
        }

        $organization->members()->updateExistingPivot($targetUser->id, ['role' => $newRole]);
    }
}