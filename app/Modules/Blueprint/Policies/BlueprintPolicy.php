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

        // Must be owner of the blueprint's org
        if (!$user->isOwnerOf($blueprint->organization)) {
            return false;
        }

        // Marketplace publish is a plan-gated feature — always check the plan
        $plan = $blueprint->organization->owner?->plan;
        if (!$plan || !$plan->has_marketplace_publish) {
            return false;
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

        // Cannot vote on own blueprints
        if ($blueprint->created_by === $user->id) {
            return false;
        }

        return true;
    }
}
