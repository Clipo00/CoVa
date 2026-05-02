<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Forms;

use App\Modules\Auth\Actions\RegisterUser;
use App\Modules\Auth\DTOs\RegisterUserData;
use App\Modules\Auth\Requests\RegisterRequest;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class RegisterForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected function rules(): array
    {
        return RegisterRequest::rules();
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function submit(RegisterUser $registerUser): void
    {
        $validated = $this->validate();

        try {
            $data = new RegisterUserData(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
            );

            $user = $registerUser->execute($data);

            auth()->login($user);

            $this->redirect(route('organizations.create'));
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $error) {
                    $this->addError($field, $error);
                }
            }
        }
    }

    public function render()
    {
        return view('auth::livewire.forms.register-form');
    }
}
