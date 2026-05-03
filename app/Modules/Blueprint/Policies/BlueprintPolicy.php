<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Policies;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;

class BlueprintPolicy
{
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
        return $blueprint->created_by === $user->id 
            || $user->isOwnerOf($blueprint->organization);
    }

    public function favorite(User $user, Blueprint $blueprint): bool
    {
        return $user->hasRoleInOrganization($blueprint->organization, ['owner', 'maintainer', 'developer']);
    }
}
