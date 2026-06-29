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
        // Marketplace must be globally enabled
        if (!config('marketplace.enabled')) {
            return false;
        }

        // Must be owner or maintainer of the blueprint's org
        $membership = $user->organizations()
            ->where('organization_id', $blueprint->organization_id)
            ->first();

        if (!$membership) {
            return false;
        }

        $role = $membership->pivot->role;

        if (!in_array($role, ['owner', 'maintainer'], true)) {
            return false;
        }

        // If billing is enabled, check plan
        if (config('marketplace.billing_enabled')) {
            // Plan belongs to the owner, not the org directly
            $plan = $blueprint->organization->owner?->plan;
            if (!$plan || !$plan->has_marketplace_publish) {
                return false;
            }
        }

        return true;
    }

    public function vote(User $user, Blueprint $blueprint): bool
    {
        // Must be public
        if (!$blueprint->is_public) {
            return false;
        }

        // Marketplace must be enabled
        if (!config('marketplace.enabled')) {
            return false;
        }

        // Must be a member of the blueprint's (marketplace) organization
        return $user->hasRoleInOrganization($blueprint->organization, ['owner', 'maintainer', 'developer']);
    }
}
