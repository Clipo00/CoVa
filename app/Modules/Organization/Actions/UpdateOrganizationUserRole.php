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
            abort(403, __('organization.no_manage_permission'));
        }

        // No se puede cambiar el rol del owner
        if ($targetUser->id === $organization->owner_id) {
            abort(403, __('organization.cannot_change_owner_role'));
        }

        // Validar que el nuevo rol es válido
        if (!in_array($newRole, ['developer', 'maintainer'])) {
            abort(422, __('organization.invalid_role'));
        }

        // Verificar que el usuario es miembro de la organización
        if (!$organization->members()->where('user_id', $targetUser->id)->exists()) {
            abort(422, __('organization.not_a_member'));
        }

        $organization->members()->updateExistingPivot($targetUser->id, ['role' => $newRole]);
    }
}