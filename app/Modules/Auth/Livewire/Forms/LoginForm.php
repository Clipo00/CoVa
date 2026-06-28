<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use App\Modules\Auth\Actions\LoginUser;
use App\Modules\Auth\DTOs\LoginUserData;
use App\Modules\Auth\Exceptions\MfaRequiredException;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return LoginRequest::rules();
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function submit(LoginUser $loginUser): void
    {
        $validated = $this->validate();

        try {
            $data = new LoginUserData(
                email: $validated['email'],
                password: $validated['password'],
                remember: $validated['remember'] ?? false,
            );

            $loginUser->execute($data);

            // Prompt first-time users to enable MFA (only if not already enabled)
            $user = auth()->user();
            if (!$user->mfa_prompted_at && !$user->mfa_enabled) {
                $user->update(['mfa_prompted_at' => now()]);
                $this->redirect(route('mfa.setup'));
                return;
            }

            $this->redirectIntended(route('dashboard'));
        } catch (MfaRequiredException $e) {
            session()->put('mfa_user_id', $e->user->id);
            $this->redirect(route('mfa.challenge'));
        } catch (ValidationException $e) {
            $this->addError('email', $e->getMessage());
        }
    }

    public function render()
    {
        return view('auth::livewire.forms.login-form');
    }
}
