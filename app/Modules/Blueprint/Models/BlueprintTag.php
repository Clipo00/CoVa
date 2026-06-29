<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlueprintTag extends Model
{
    protected $fillable = [
        'blueprint_id',
        'tag',
    ];

    protected $table = 'blueprint_tags';

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }
}
