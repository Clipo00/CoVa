<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
    ];

    public function blueprints()
    {
        return $this->hasMany(\App\Modules\Blueprint\Models\Blueprint::class);
    }
}
