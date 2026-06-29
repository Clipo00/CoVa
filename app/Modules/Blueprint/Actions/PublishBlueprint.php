<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PublishBlueprint
{
    public function execute(Blueprint $blueprint, User $user): Blueprint
    {
        // 1. Check marketplace enabled (feature flag, not auth)
        if (!config('marketplace.enabled')) {
            throw new HttpException(503, __('blueprint.publish_marketplace_disabled'));
        }

        // 2. Check billing gate (feature flag, not auth)
        if (config('marketplace.billing_enabled')) {
            $plan = $user->plan;
            if (!$plan || !$plan->has_marketplace_publish) {
                throw new HttpException(403, __('blueprint.publish_plan_required'));
            }
        }

        // 3. Ensure blueprint is not already public
        if ($blueprint->is_public) {
            throw new HttpException(409, __('blueprint.publish_already_public'));
        }

        // 4. Resolve marketplace organization
        $marketplaceOrg = Organization::where('slug', 'cova-marketplace')->firstOrFail();

        // 5. Transfer and make public
        $blueprint->update([
            'organization_id' => $marketplaceOrg->id,
            'is_public' => true,
        ]);

        // 6. Clear all secret variable values so they are never exposed publicly.
        //    The original creator has a local copy that keeps the secrets.
        $blueprint->variables()
            ->where('is_secret', true)
            ->update(['default_value' => '']);

        return $blueprint->fresh();
    }
}
