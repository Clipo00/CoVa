<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Modules\Auth\Notifications\ResetPasswordNotification;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintFavorite;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements CanResetPasswordContract, MustVerifyEmailContract
{
    use MustVerifyEmail, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'locale',
        'avatar',
        'password',
        'plan_id',
        'is_system',
        'mfa_enabled',
        'mfa_prompted_at',
        'onboarding_completed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
            'mfa_prompted_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function mfaCodes()
    {
        return $this->hasMany(MfaCode::class);
    }

    public function mfaTrustedDevices()
    {
        return $this->hasMany(MfaTrustedDevice::class);
    }

    public function ownedOrganizations()
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function favorites()
    {
        return $this->hasMany(BlueprintFavorite::class);
    }

    public function favoriteBlueprints()
    {
        return $this->belongsToMany(Blueprint::class, 'blueprint_favorites');
    }

    /**
     * Get avatar URL or default initials avatar
     *
     * Para archivos locales: usa URL relativa /storage/avatars/...
     * Para S3: usa Storage::disk('s3')->url()
     */
    public function avatarUrl(): string
    {
        if ($this->avatar) {
            $isS3 = config('filesystems.disks.avatars.driver') === 's3';

            if ($isS3) {
                return Storage::disk('s3')->url($this->avatar);
            }

            return asset('storage/avatars/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=4f46e5&color=fff';
    }

    /**
     * Get initials for avatar fallback
     */
    public function initials(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper($word[0] ?? '');
        }

        return $initials;
    }

    /**
     * Check if user has a specific role in an organization
     */
    public function hasRoleInOrganization(Organization $organization, string|array $roles): bool
    {
        $member = $this->organizations()
            ->where('organization_id', $organization->id)
            ->first();

        if (!$member) {
            return false;
        }

        $userRole = $member->pivot->role;

        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }

        return $userRole === $roles;
    }

    /**
     * Check if user is owner of an organization
     */
    public function isOwnerOf(Organization $organization): bool
    {
        return $organization->owner_id === $this->id;
    }

    /**
     * Check if user can manage members in an organization
     * Owner and Maintainer can manage members
     */
    public function canManageMembers(Organization $organization): bool
    {
        return $this->isOwnerOf($organization)
            || $this->hasRoleInOrganization($organization, ['owner', 'maintainer']);
    }

    /**
     * Check if user can create blueprints in an organization
     * All roles can create blueprints
     */
    public function canCreateBlueprints(Organization $organization): bool
    {
        return $this->hasRoleInOrganization($organization, ['owner', 'maintainer', 'developer']);
    }

    /**
     * Check if user can delete an organization
     * Only owner can delete
     */
    public function canDeleteOrganization(Organization $organization): bool
    {
        return $this->isOwnerOf($organization);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
