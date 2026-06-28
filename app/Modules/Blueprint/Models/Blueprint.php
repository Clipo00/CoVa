<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blueprint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'organization_id',
        'category_id',
        'slug',
        'title',
        'description',
        'is_public',
        'aggregate_score',
        'tabs_config',
        'created_by',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'aggregate_score' => 'integer',
        'tabs_config' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function variables()
    {
        return $this->hasMany(BlueprintVariable::class)->orderBy('sort_order');
    }

    public function variablesUnsorted()
    {
        return $this->hasMany(BlueprintVariable::class);
    }

    public function favorites()
    {
        return $this->hasMany(BlueprintFavorite::class);
    }

    public function votes()
    {
        return $this->hasMany(BlueprintVote::class);
    }

    public function favoritedBy(User $user): bool
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }
}
