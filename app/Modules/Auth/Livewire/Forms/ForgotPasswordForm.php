<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPasswordForm extends Component
{
    public string $email = '';

    public string $statusMessage = '';

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function sendResetLink(): void
    {
        $validated = $this->validate();

        $status = Password::sendResetLink(
            ['email' => $validated['email']],
        );

        $this->statusMessage = __('auth.password_reset_sent');
    }

    public function render()
    {
        return view('auth::livewire.forms.forgot-password-form');
    }
}
