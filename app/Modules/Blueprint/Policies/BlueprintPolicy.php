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
            \Illuminate\Support\Facades\Log::warning('Publish denied: marketplace disabled', ['user_id' => $user->id]);
            return false;
        }

        // Must be owner of the blueprint's org
        $org = $blueprint->organization;
        if (!$org) {
            \Illuminate\Support\Facades\Log::warning('Publish denied: organization not found', ['user_id' => $user->id, 'blueprint_id' => $blueprint->id]);
            return false;
        }
        if (!$user->isOwnerOf($org)) {
            \Illuminate\Support\Facades\Log::warning('Publish denied: user is not owner', [
                'user_id' => $user->id,
                'org_id' => $org->id,
                'org_owner_id' => $org->owner_id,
            ]);
            return false;
        }

        // If billing is enabled, check plan
        if (config('marketplace.billing_enabled')) {
            $plan = $org->owner?->plan;
            if (!$plan || !$plan->has_marketplace_publish) {
                \Illuminate\Support\Facades\Log::warning('Publish denied: plan check failed', [
                    'user_id' => $user->id,
                    'plan' => $plan?->name ?? 'null',
                    'has_marketplace_publish' => $plan?->has_marketplace_publish ?? 'null',
                ]);
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
