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
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements CanResetPasswordContract, MustVerifyEmailContract
{
    use HasApiTokens, MustVerifyEmail, Notifiable;

    protected static function booted(): void
    {
        static::retrieved(function (User $user) {
            $user->revertTrialIfExpired();
        });
    }

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
        'trial_ends_at',
        'trial_used_at',
        'trial_expiry_notified_at',
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
            'trial_ends_at' => 'datetime',
            'trial_used_at' => 'datetime',
            'trial_expiry_notified_at' => 'datetime',
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
     * Check if user is on an active Pro trial.
     */
    public function isOnProTrial(): bool
    {
        if ($this->trial_ends_at === null) {
            return false;
        }

        $proPlan = Plan::where('slug', 'pro')->first();

        return $this->plan_id === $proPlan?->id && $this->trial_ends_at->isFuture();
    }

    /**
     * Revert trial to Free plan if trial has expired.
     * Returns true if reverted, false otherwise.
     */
    public function revertTrialIfExpired(): bool
    {
        if ($this->trial_ends_at === null) {
            return false;
        }

        if ($this->trial_ends_at->isFuture()) {
            return false;
        }

        $freePlan = Plan::where('slug', 'free')->first();
        if (!$freePlan) {
            return false;
        }

        $this->update(['plan_id' => $freePlan->id]);

        return true;
    }

    /**
     * Days remaining in trial, or null if not on trial.
     */
    public function trialDaysRemaining(): ?int
    {
        if (!$this->isOnProTrial()) {
            return null;
        }

        return max(0, (int) now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Check if user has API access via:
     * 1. Own plan (Pro/Enterprise) or active Pro trial
     * 2. Membership in any organization whose owner has Pro/Enterprise
     */
    public function hasApiAccess(): bool
    {
        // Own plan or trial
        if ($this->isOnProTrial()) {
            return true;
        }

        if ($this->plan?->has_api_access) {
            return true;
        }

        // Belongs to an org whose owner has API access
        return $this->organizations()
            ->whereHas('owner', function ($query) {
                $query->whereHas('plan', function ($q) {
                    $q->where('has_api_access', true);
                });
            })
            ->exists();
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
