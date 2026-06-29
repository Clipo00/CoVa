<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'user_id',
        'blueprint_id',
        'vote',
    ];

    protected $casts = [
        'vote' => 'integer',
    ];

    protected $table = 'blueprint_votes';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }
}
