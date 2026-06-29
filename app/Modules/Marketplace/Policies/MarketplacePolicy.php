<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarketplacePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can subscribe to a blueprint.
     * User must have at least one organization.
     */
    public function subscribe(User $user, Blueprint $blueprint): bool
    {
        return $user->organizations()->exists();
    }

    /**
     * Determine if the user can vote on a blueprint.
     * Blueprint must be public.
     */
    public function vote(User $user, Blueprint $blueprint): bool
    {
        return $blueprint->is_public;
    }
}
