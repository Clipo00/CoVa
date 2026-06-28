<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use App\Modules\Auth\Actions\SendMfaCode;
use App\Modules\Auth\Actions\VerifyMfaCode;
use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class MfaChallengeForm extends Component
{
    public string $code = '';

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:6'],
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function submit(VerifyMfaCode $verifyMfaCode): void
    {
        $this->validate();

        $userId = session('mfa_user_id', auth()->id());
        $throttleKey = 'mfa-challenge:'.($userId ?? request()->ip());

        // OWASP A07:2025 — prevent brute-force on MFA codes (5 attempts/min)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('code', __('auth.throttle', ['seconds' => $seconds]));
            return;
        }

        RateLimiter::hit($throttleKey, 60);

        if ($userId === null) {
            $this->addError('code', __('auth.mfa_invalid_code'));
            return;
        }

        $user = User::find($userId);

        if ($user === null) {
            $this->addError('code', __('auth.mfa_invalid_code'));
            return;
        }

        if (!$verifyMfaCode->execute($user, $this->code)) {
            $this->addError('code', __('auth.mfa_invalid_code'));
            $this->code = '';
            return;
        }

        Auth::login($user);
        session()->forget('mfa_user_id');

        $this->redirectIntended(route('dashboard'));
    }

    public function resend(SendMfaCode $sendMfaCode): void
    {
        $userId = session('mfa_user_id', auth()->id());
        $throttleKey = 'mfa-resend:'.($userId ?? request()->ip());

        // OWASP A07:2025 — prevent email bombing (3 resends/min)
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('code', __('auth.throttle', ['seconds' => $seconds]));
            return;
        }

        RateLimiter::hit($throttleKey, 60);

        if ($userId === null) {
            $this->addError('code', __('auth.mfa_invalid_code'));
            return;
        }

        $user = User::find($userId);

        if ($user === null) {
            $this->addError('code', __('auth.mfa_invalid_code'));
            return;
        }

        $sendMfaCode->execute($user);
        $this->code = '';
        $this->dispatch('notify', message: __('auth.mfa_code_sent'));
    }

    public function render()
    {
        return view('auth::livewire.forms.mfa-challenge-form');
    }
}
