<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ChangePasswordForm extends Component
{
    public string $password = '';

    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $user = auth()->user();

        $user->update([
            'password' => $this->password,
            'password_change_required' => false,
        ]);

        $this->redirect(session()->pull('url.intended', route('dashboard')));
    }

    public function render()
    {
        return view('auth::livewire.forms.change-password-form');
    }
}
