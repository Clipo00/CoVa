<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscribed_blueprint_id',
        'copied_blueprint_id',
        'notify_on_update',
    ];

    protected $casts = [
        'notify_on_update' => 'boolean',
    ];

    protected $attributes = [
        'notify_on_update' => true,
    ];

    protected $table = 'blueprint_subscriptions';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscribedBlueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class, 'subscribed_blueprint_id');
    }

    public function copiedBlueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class, 'copied_blueprint_id');
    }
}
