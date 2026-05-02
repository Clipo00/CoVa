<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use Illuminate\Database\Eloquent\Model;

class BlueprintVariable extends Model
{
    protected $fillable = [
        'blueprint_id',
        'key',
        'type',
        'default_value',
        'is_interactive',
        'is_secret',
        'section',
        'sort_order',
    ];

    protected $casts = [
        'is_interactive' => 'boolean',
        'is_secret' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function blueprint()
    {
        return $this->belongsTo(Blueprint::class);
    }
}
