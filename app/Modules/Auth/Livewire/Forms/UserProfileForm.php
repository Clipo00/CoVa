<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use App\Modules\Auth\Actions\UpdateUserProfile;
use App\Modules\Auth\DTOs\UpdateUserProfileData;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserProfileForm extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar = null; // Livewire temporary upload
    public ?string $currentPassword = null;
    public ?string $newPassword = null;
    public ?string $newPasswordConfirmation = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore(auth()->id()),
            ],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max
            'currentPassword' => ['nullable', 'required_with:newPassword'],
            'newPassword' => ['nullable', 'min:8', 'confirmed'],
            'newPasswordConfirmation' => ['nullable'],
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function submit(UpdateUserProfile $updateUserProfile): void
    {
        $validated = $this->validate();

        // Verify current password if changing password
        if ($this->newPassword && !\Hash::check($this->currentPassword, auth()->user()->password)) {
            $this->addError('currentPassword', 'La contraseña actual es incorrecta');
            return;
        }

        $data = new UpdateUserProfileData(
            name: $validated['name'],
            email: $validated['email'],
            avatar: $this->avatar,
            currentPassword: $this->currentPassword,
            newPassword: $this->newPassword,
        );

        $updateUserProfile->execute(auth()->user(), $data);

        // Reset password fields
        $this->currentPassword = null;
        $this->newPassword = null;
        $this->newPasswordConfirmation = null;
        $this->avatar = null;

        $this->dispatch('notify', message: 'Perfil actualizado correctamente');
    }

    public function render()
    {
        return view('auth::livewire.forms.user-profile-form');
    }
}
