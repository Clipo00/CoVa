<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;

class BlueprintPolicy
{
    public function create(User $user, Organization $organization): bool
    {
        return $user->hasRoleInOrganization($organization, ['owner', 'maintainer', 'developer']);
    }

    public function view(User $user, Blueprint $blueprint): bool
    {
        return $user->hasRoleInOrganization($blueprint->organization, ['owner', 'maintainer', 'developer']);
    }

    public function update(User $user, Blueprint $blueprint): bool
    {
        return $blueprint->created_by === $user->id 
            || $user->hasRoleInOrganization($blueprint->organization, ['owner', 'maintainer']);
    }

    public function delete(User $user, Blueprint $blueprint): bool
    {
        return $user->isOwnerOf($blueprint->organization);
    }

    public function favorite(User $user, Blueprint $blueprint): bool
    {
        return $user->hasRoleInOrganization($blueprint->organization, ['owner', 'maintainer', 'developer']);
    }

    public function publish(User $user, Blueprint $blueprint): bool
    {
        $membershipRole = null;
        $member = $user->organizations()
            ->where('organization_id', $blueprint->organization_id)
            ->first();

        if (!$member) {
            return false;
        }

        $membershipRole = $member->pivot->role;

        if (!in_array($membershipRole, ['owner', 'maintainer'], true)) {
            return false;
        }

        // Global feature flag: if marketplace is disabled, nobody can publish
        if (!config('app.marketplace_enabled', false)) {
            return false;
        }

        // Plan is owned by the user, not the org. Check owner's plan.
        return $blueprint->organization->owner->plan->has_marketplace_publish;
    }
}
