<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Actions\NotifySubscribers;
use App\Modules\Marketplace\Models\Subscription;

class DeleteBlueprint
{
    public function execute(Blueprint $blueprint): void
    {
        // If the blueprint is public, notify subscribers and unlink subscriptions
        if ($blueprint->is_public) {
            if ($blueprint->subscribers_count > 0) {
                NotifySubscribers::dispatch($blueprint->id, 'blueprint_deleted');
            }

            // Unlink subscriptions (copies remain accessible, just unlinked)
            Subscription::where('subscribed_blueprint_id', $blueprint->id)
                ->update(['subscribed_blueprint_id' => null]);
        }

        $blueprint->delete();
    }
}
