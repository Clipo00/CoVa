<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function blueprints(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Blueprint\Models\Blueprint::class,
            'blueprint_tag',
            'tag_id',
            'blueprint_id',
        );
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function findOrCreate(string $name): self
    {
        $slug = str($name)->slug()->value();

        return static::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name],
        );
    }
}
