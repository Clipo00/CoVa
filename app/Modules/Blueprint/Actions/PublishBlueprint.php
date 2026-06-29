<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\ValueObjects\Uuid;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PublishBlueprint
{
    /**
     * Publish a blueprint to the marketplace.
     *
     * Creates a copy in the marketplace organization with secrets cleared,
     * marks the original as public, and creates a subscription so the
     * creator can sync updates later.
     */
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

        // 5. Create a copy in the marketplace org (secrets cleared on copy)
        $copy = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $marketplaceOrg->id,
            'slug' => $blueprint->slug,
            'title' => $blueprint->title,
            'description' => $blueprint->description,
            'category_id' => $blueprint->category_id,
            'is_public' => true,
            'tabs_config' => $blueprint->tabs_config,
            'created_by' => $user->id,
        ]);

        // 5b. Copy variables — clear secret values on the marketplace copy
        foreach ($blueprint->variables as $variable) {
            $copy->variables()->create([
                'key' => $variable->key,
                'type' => $variable->type,
                'default_value' => $variable->is_secret ? '' : $variable->default_value,
                'is_interactive' => $variable->is_interactive,
                'is_secret' => $variable->is_secret,
                'section' => $variable->section,
                'section_color' => $variable->section_color,
                'sort_order' => $variable->sort_order,
            ]);
        }

        // 5c. Copy tags
        foreach ($blueprint->tags as $tag) {
            $copy->tags()->create(['tag' => $tag->tag]);
        }

        // 6. Mark original as public (stays in user's org)
        $blueprint->update(['is_public' => true]);

        // 7. Create a subscription so the creator can sync updates later.
        //    The creator is subscribed to their own marketplace copy,
        //    and the copied_blueprint_id points back to the original.
        $exists = DB::table('blueprint_subscriptions')
            ->where('user_id', $user->id)
            ->where('subscribed_blueprint_id', $copy->id)
            ->exists();

        if (!$exists) {
            DB::table('blueprint_subscriptions')->insert([
                'user_id' => $user->id,
                'subscribed_blueprint_id' => $copy->id,
                'copied_blueprint_id' => $blueprint->id,
                'notify_on_update' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $blueprint->fresh();
    }
}
