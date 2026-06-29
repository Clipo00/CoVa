<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Models\Subscription;
use App\Modules\Shared\ValueObjects\Uuid;
use RuntimeException;

class SubscribeToBlueprint
{
    /**
     * Execute the subscription: creates a copy of the blueprint for the user's organization.
     *
     * @throws RuntimeException When already subscribed or user has no organization.
     */
    public function execute(User $user, Blueprint $blueprint): Blueprint
    {
        // 1. Check: user is not already subscribed to this blueprint
        $alreadySubscribed = Subscription::where('user_id', $user->id)
            ->where('subscribed_blueprint_id', $blueprint->id)
            ->exists();

        if ($alreadySubscribed) {
            throw new RuntimeException(__('marketplace.already_subscribed'));
        }

        // 2. Get user's first organization
        $organization = $user->organizations()->first();

        if (!$organization) {
            throw new RuntimeException(__('marketplace.no_organization'));
        }

        // 3. Check plan blueprint limit — subscriptions consume a slot
        $plan = $organization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;

        if ($maxBlueprints !== null && $organization->blueprints()->count() >= $maxBlueprints) {
            throw new MaxBlueprintsReachedException($maxBlueprints, $plan->name);
        }

        // 4. Create copy of blueprint
        $copy = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $organization->id,
            'slug' => $blueprint->slug . '-' . substr((string) Uuid::generate(), 0, 8),
            'title' => $blueprint->title,
            'description' => $blueprint->description,
            'category_id' => $blueprint->category_id,
            'is_public' => false,
            'tabs_config' => $blueprint->tabs_config,
            'created_by' => $user->id,
        ]);

        // 4. Copy variables (clear secret default values for security)
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

        // 5. Create Subscription record
        Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $blueprint->id,
            'copied_blueprint_id' => $copy->id,
            'notify_on_update' => true,
        ]);

        // 6. Increment original's subscribers_count
        $blueprint->increment('subscribers_count');

        return $copy;
    }
}
