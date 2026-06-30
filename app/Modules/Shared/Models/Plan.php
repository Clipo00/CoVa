<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Models\Organization;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'max_organizations_per_user',
        'max_blueprints_per_org',
        'max_members_per_org',
        'max_variables_per_blueprint',
        'has_api_access',
        'has_marketplace_publish',
        'price_monthly',
        'is_active',
    ];

    protected $casts = [
        'max_organizations_per_user' => 'integer',
        'max_blueprints_per_org' => 'integer',
        'max_members_per_org' => 'integer',
        'max_variables_per_blueprint' => 'integer',
        'has_api_access' => 'boolean',
        'has_marketplace_publish' => 'boolean',
        'price_monthly' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}
