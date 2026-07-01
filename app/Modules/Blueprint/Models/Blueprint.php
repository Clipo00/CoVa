<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Models;

use App\Models\Tag;
use App\Modules\Auth\Models\User;
use App\Modules\Marketplace\Models\Vote;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blueprint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'organization_id',
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
        'votes_count' => 'integer',
        'subscribers_count' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
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

    public function favoritedBy(User $user): bool
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blueprint_tag');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'blueprint_id');
    }
}
