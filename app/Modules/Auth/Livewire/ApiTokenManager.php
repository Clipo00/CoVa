<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire;

use App\Modules\Auth\Actions\CreateApiToken;
use App\Modules\Auth\Actions\RevokeApiToken;
use App\Modules\Organization\Models\Organization;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class ApiTokenManager extends Component
{
    public Collection $tokens;

    public string $tokenName = '';

    public string $expiresAt = '';

    public string $password = '';

    public ?string $newPlainTextToken = null;

    /**
     * Get the token value without Sanctum's ID prefix (e.g., "1|abc123" → "abc123").
     */
    public function tokenWithoutPrefix(): string
    {
        if ($this->newPlainTextToken === null) {
            return '';
        }

        $parts = explode('|', $this->newPlainTextToken, 2);

        return $parts[1] ?? $this->newPlainTextToken;
    }

    public string $revokePassword = '';

    public ?int $revokeTokenId = null;

    public bool $showCreateForm = false;

    public bool $isFreePlan = false;

    /** @var Collection<int, Organization> Organizations whose owner has API access */
    public Collection $eligibleOrganizations;

    public ?int $selectedOrganizationId = null;

    public function mount(): void
    {
        $user = auth()->user();

        $this->tokens = $user->tokens;
        $this->isFreePlan = !$user->hasApiAccess();

        $this->eligibleOrganizations = $user->organizations()
            ->whereHas('owner', function ($query) {
                $query->whereHas('plan', function ($q) {
                    $q->where('has_api_access', true);
                });
            })
            ->get();

        // Auto-select if only one eligible organization
        if ($this->eligibleOrganizations->count() === 1) {
            $this->selectedOrganizationId = $this->eligibleOrganizations->first()->id;
        }
    }

    public function createToken(CreateApiToken $action): void
    {
        $rules = [
            'tokenName' => ['required', 'string', 'max:255'],
            'expiresAt' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addYear()->format('Y-m-d')],
            'password' => ['required', 'string'],
        ];

        // Require org selection if multiple eligible orgs exist
        if ($this->eligibleOrganizations->count() > 1) {
            $rules['selectedOrganizationId'] = ['required', 'integer', 'in:' . $this->eligibleOrganizations->pluck('id')->join(',')];
        }

        $this->validate($rules);

        $this->ensureRateLimit();

        try {
            $organizationId = $this->selectedOrganizationId ?? $this->eligibleOrganizations->first()?->id;

            $plainTextToken = $action->execute(
                user: auth()->user(),
                name: $this->tokenName,
                expiresAt: \Carbon\Carbon::parse($this->expiresAt),
                password: $this->password,
                organizationId: $organizationId,
            );

            $this->newPlainTextToken = $plainTextToken;
            $this->tokenName = '';
            $this->expiresAt = '';
            $this->password = '';
            $this->showCreateForm = false;
            $this->reloadTokens();
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());
        }
    }

    public function dismissNewToken(): void
    {
        $this->newPlainTextToken = null;
        $this->reloadTokens();
    }

    public function confirmRevoke(int $tokenId): void
    {
        $this->revokeTokenId = $tokenId;
    }

    public function revokeToken(RevokeApiToken $action): void
    {
        $this->validate([
            'revokePassword' => ['required', 'string'],
        ]);

        try {
            $action->execute(
                user: auth()->user(),
                tokenId: $this->revokeTokenId,
                password: $this->revokePassword,
            );

            $this->revokeTokenId = null;
            $this->revokePassword = '';
            $this->reloadTokens();

            $this->dispatch('notify', message: __('auth.token_revoked'));
        } catch (ValidationException $e) {
            $this->setErrorBag($e->errors());
        }
    }

    public function cancelRevoke(): void
    {
        $this->revokeTokenId = null;
        $this->revokePassword = '';
    }

    private function reloadTokens(): void
    {
        $this->tokens = auth()->user()->tokens()->get();
    }

    private function ensureRateLimit(): void
    {
        $limiter = app(RateLimiter::class);
        $key = 'create-api-token:' . auth()->id();

        if ($limiter->tooManyAttempts($key, 10)) {
            $seconds = $limiter->availableIn($key);

            throw ValidationException::withMessages([
                'tokenName' => [__('auth.throttle', ['seconds' => $seconds])],
            ]);
        }

        $limiter->hit($key, 60);
    }

    public function render()
    {
        return view('auth::livewire.api-token-manager');
    }
}
