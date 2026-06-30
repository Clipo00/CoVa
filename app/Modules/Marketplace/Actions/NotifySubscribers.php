<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Actions;

use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Models\Notification;
use App\Modules\Marketplace\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifySubscribers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $blueprintId,
        public string $type,
    ) {
        //
    }

    public function handle(): void
    {
        // Get blueprint (even if soft-deleted)
        $blueprint = Blueprint::withTrashed()->find($this->blueprintId);

        if ($blueprint === null) {
            return;
        }

        // Get all subscriptions that want notifications
        Subscription::where('subscribed_blueprint_id', $this->blueprintId)
            ->where('notify_on_update', true)
            ->chunk(100, function ($subscriptions) use ($blueprint) {
                $notifications = [];

                foreach ($subscriptions as $subscription) {
                    $notifications[] = [
                        'user_id' => $subscription->user_id,
                        'type' => $this->type,
                        'data' => json_encode([
                            'blueprint_uuid' => $blueprint->uuid,
                            'blueprint_title' => $blueprint->title,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (! empty($notifications)) {
                    Notification::insert($notifications);
                }
            });
    }
}
