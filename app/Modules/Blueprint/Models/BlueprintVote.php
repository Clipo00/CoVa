<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlueprintVote extends Model
{
    protected $fillable = [
        'user_id',
        'blueprint_id',
        'vote',
    ];

    // NOTA: La constraint unique(['user_id', 'blueprint_id']) está definida a nivel de DB.
    // Esto evita votos duplicados (un usuario solo puede votar una vez por blueprint).

    protected $casts = [
        'vote' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }
}
