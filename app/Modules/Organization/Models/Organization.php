<?php

declare(strict_types=1);

namespace App\Modules\Organization\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Shared\Models\Plan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Plan is owned by the user, not the organization.
     * This accessor delegates to the owner's plan transparently.
     */
    public function getPlanAttribute(): ?Plan
    {
        return $this->owner?->plan;
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function invitations()
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    public function blueprints()
    {
        return $this->hasMany(\App\Modules\Blueprint\Models\Blueprint::class);
    }
}
