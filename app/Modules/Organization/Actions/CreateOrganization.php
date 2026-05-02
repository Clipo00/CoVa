<?php

declare(strict_types=1);

namespace App\Modules\Organization\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Exceptions\MaxOrganizationsReachedException;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\ValueObjects\Slug;

class CreateOrganization
{
    public function execute(User $user, string $name, string $slug): Organization
    {
        $plan = $user->plan;

        if ($plan === null) {
            throw new \RuntimeException('User does not have a plan assigned.');
        }

        $maxOrganizations = $plan->max_organizations_per_user;

        if ($maxOrganizations !== null && $user->ownedOrganizations()->count() >= $maxOrganizations) {
            throw new MaxOrganizationsReachedException($maxOrganizations, $plan->name);
        }

        $slug = new Slug($slug);

        $organization = Organization::create([
            'slug' => (string) $slug,
            'name' => $name,
            'owner_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        $organization->members()->attach($user->id, ['role' => 'owner']);

        return $organization;
    }
}
