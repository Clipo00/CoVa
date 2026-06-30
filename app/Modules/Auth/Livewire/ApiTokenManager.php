<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire;

use App\Modules\Auth\Actions\CreateApiToken;
use App\Modules\Auth\Actions\RevokeApiToken;
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

    public string $revokePassword = '';

    public ?int $revokeTokenId = null;

    public bool $showCreateForm = false;

    public bool $isFreePlan = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->tokens = $user->tokens;
        $this->isFreePlan = !$user->hasApiAccess();
    }

    public function createToken(CreateApiToken $action): void
    {
        $this->validate([
            'tokenName' => ['required', 'string', 'max:255'],
            'expiresAt' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:' . now()->addYear()->format('Y-m-d')],
            'password' => ['required', 'string'],
        ]);

        $this->ensureRateLimit();

        try {
            $plainTextToken = $action->execute(
                user: auth()->user(),
                name: $this->tokenName,
                expiresAt: \Carbon\Carbon::parse($this->expiresAt),
                password: $this->password,
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
