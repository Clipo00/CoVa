<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Component;

class ResetPasswordForm extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @param array<string, mixed> $params
     */
    public function mount(string $token, string $email = ''): void
    {
        $this->token = $token;
        $this->email = $email;
    }

    protected function rules(): array
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
            'password_confirmation' => 'required|same:password',
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function resetPassword(): void
    {
        $this->validate();

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();

                auth()->login($user);
                auth()->logoutOtherDevices($password);

                session()->regenerate();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->redirectIntended(route('dashboard'));
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('auth::livewire.forms.reset-password-form');
    }
}
