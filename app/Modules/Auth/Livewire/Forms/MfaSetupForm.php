<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use App\Modules\Auth\Actions\SendMfaCode;
use Livewire\Component;

class MfaSetupForm extends Component
{
    public function enable(SendMfaCode $sendMfaCode): void
    {
        $user = auth()->user();

        // Guard: if MFA is already enabled, skip to intended destination
        if ($user->mfa_enabled) {
            $this->redirect(route('dashboard'));

            return;
        }

        $user->update(['mfa_enabled' => true]);
        $sendMfaCode->execute($user);
        session()->put('mfa_user_id', $user->id);
        session()->flash('mfa_activated', true);
        $this->redirect(route('mfa.challenge'));
    }

    public function skip(): void
    {
        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('auth::livewire.forms.mfa-setup-form');
    }
}
