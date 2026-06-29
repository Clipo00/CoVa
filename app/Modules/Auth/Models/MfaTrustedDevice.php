<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MfaTrustedDevice extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'device_fingerprint',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this trusted device is still valid.
     *
     * The device must not be expired, and the fingerprint must match
     * the current request (User-Agent + IP subnet).
     */
    public function isValid(string $fingerprint): bool
    {
        return $this->expires_at->isFuture()
            && hash_equals($this->device_fingerprint, $fingerprint);
    }
}
