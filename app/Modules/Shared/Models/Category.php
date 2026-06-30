<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models;

use App\Modules\Blueprint\Models\Blueprint;
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
        return $this->hasMany(Blueprint::class);
    }
}
