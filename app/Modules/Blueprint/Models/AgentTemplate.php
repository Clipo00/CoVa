<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $display_name
 * @property string $content
 * @property array $skills
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AgentTemplate extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'content',
        'skills',
    ];

    protected $casts = [
        'skills' => 'array',
    ];
}
