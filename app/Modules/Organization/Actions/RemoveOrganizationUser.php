<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RemoveOrganizationUser
{
    public function execute(Organization $organization, User $targetUser, User $actor): void
    {
        // 1. Validate actor is owner
        if (!$actor->isOwnerOf($organization)) {
            throw new HttpException(403, __('organization.no_manage_permission'));
        }

        // 2. Prevent self-removal
        if ($targetUser->id === $actor->id) {
            throw new HttpException(403, __('organization.cannot_remove_self'));
        }

        // 3. Prevent removal of another owner
        if ($targetUser->isOwnerOf($organization)) {
            throw new HttpException(403, __('organization.cannot_remove_owner'));
        }

        // 3. Ensure target is a member
        $membership = $organization->members()
            ->where('user_id', $targetUser->id)
            ->first();

        if (!$membership) {
            throw new HttpException(404, __('organization.not_a_member'));
        }

        // 4. Transaction: reassign blueprints, detach pivot
        DB::transaction(function () use ($organization, $targetUser, $actor) {
            // Reassign blueprints created by the removed user to the owner
            $organization->blueprints()
                ->where('created_by', $targetUser->id)
                ->update(['created_by' => $organization->owner_id]);

            // Detach from organization
            $organization->members()->detach($targetUser->id);
        });
    }
}
