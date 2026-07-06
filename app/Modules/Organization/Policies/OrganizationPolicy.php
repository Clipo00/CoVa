<?php

declare(strict_types=1);

namespace App\Modules\Organization\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $user->hasRoleInOrganization($organization, ['owner', 'maintainer', 'developer']);
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->hasRoleInOrganization($organization, ['owner', 'maintainer']);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->isOwnerOf($organization);
    }

    public function invite(User $user, Organization $organization): bool
    {
        return $user->canManageMembers($organization);
    }

    public function manageMembers(User $user, Organization $organization): bool
    {
        return $user->isOwnerOf($organization);
    }

    public function createBlueprint(User $user, Organization $organization): bool
    {
        return $user->canCreateBlueprints($organization);
    }

    public function updateMemberRole(User $user, Organization $organization): bool
    {
        return $user->isOwnerOf($organization);
    }

    public function removeMember(User $user, Organization $organization): bool
    {
        return $user->isOwnerOf($organization);
    }

    public function revokeInvitation(User $user, Organization $organization): bool
    {
        return $user->isOwnerOf($organization);
    }

    public function resendInvitation(User $user, Organization $organization): bool
    {
        return $user->isOwnerOf($organization);
    }
}
