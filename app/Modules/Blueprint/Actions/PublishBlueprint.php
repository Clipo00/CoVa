<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Actions\NotifySubscribers;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\ValueObjects\Uuid;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PublishBlueprint
{
    /**
     * Publish or sync a blueprint to the marketplace.
     *
     * First publish: creates a copy in the marketplace org with secrets cleared,
     * marks the original as public, and creates a subscription.
     *
     * Re-publish (sync): updates the existing marketplace copy with the
     * latest data from the original and notifies subscribers.
     */
    public function execute(Blueprint $blueprint, User $user): Blueprint
    {
        // 1. Check marketplace enabled (feature flag, not auth)
        if (!config('marketplace.enabled')) {
            throw new HttpException(503, __('blueprint.publish_marketplace_disabled'));
        }

        // 2. Marketplace publish is plan-gated — always check the plan
        $plan = $user->plan;
        if (!$plan || !$plan->has_marketplace_publish) {
            throw new HttpException(403, __('blueprint.publish_plan_required'));
        }

        $marketplaceOrg = Organization::where('slug', 'cova-marketplace')->firstOrFail();

        // 3. Check if already published — find the marketplace copy via subscription
        $subscription = DB::table('blueprint_subscriptions')
            ->where('user_id', $user->id)
            ->where('copied_blueprint_id', $blueprint->id)
            ->first();

        if ($subscription) {
            // === SYNC: Update existing marketplace copy ===
            $copy = Blueprint::find($subscription->subscribed_blueprint_id);

            if (!$copy) {
                throw new HttpException(500, __('blueprint.publish_sync_copy_missing'));
            }

            $this->syncCopy($blueprint, $copy);

            // Notify subscribers about the update
            app(NotifySubscribers::class)->execute($copy, 'updated');

            return $blueprint->fresh();
        }

        // === FIRST PUBLISH: Create marketplace copy ===
        $copy = $this->createMarketplaceCopy($blueprint, $user, $marketplaceOrg);

        // Mark original as public
        $blueprint->update(['is_public' => true]);

        // Create subscription linking creator to marketplace copy
        DB::table('blueprint_subscriptions')->insert([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $copy->id,
            'copied_blueprint_id' => $blueprint->id,
            'notify_on_update' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $blueprint->fresh();
    }

    /**
     * Create a new marketplace copy from the original blueprint.
     */
    private function createMarketplaceCopy(Blueprint $blueprint, User $user, Organization $marketplaceOrg): Blueprint
    {
        // Ensure unique slug within marketplace org
        $slug = $this->uniqueMarketplaceSlug($blueprint->slug, $marketplaceOrg->id);

        $copy = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $marketplaceOrg->id,
            'slug' => $slug,
            'title' => $blueprint->title,
            'description' => $blueprint->description,
            'is_public' => true,
            'tabs_config' => $blueprint->tabs_config,
            'created_by' => $user->id,
        ]);

        $this->syncCopy($blueprint, $copy);

        return $copy;
    }

    /**
     * Generate a unique slug for the marketplace org by appending a counter
     * if the base slug already exists.
     */
    private function uniqueMarketplaceSlug(string $baseSlug, int $organizationId): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (Blueprint::where('organization_id', $organizationId)
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Sync variables and tags from original to marketplace copy.
     * Secret values are always cleared on the copy.
     */
    private function syncCopy(Blueprint $original, Blueprint $copy): void
    {
        // Update basic fields
        $copy->update([
            'title' => $original->title,
            'description' => $original->description,
            'tabs_config' => $original->tabs_config,
        ]);

        // Sync variables: delete old, recreate with secrets cleared
        $copy->variables()->delete();
        foreach ($original->variables as $variable) {
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

        // Sync tags
        $copy->tags()->sync($original->tags->pluck('id'));
    }
}
