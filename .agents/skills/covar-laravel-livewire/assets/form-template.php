<?php

declare(strict_types=1);

namespace App\Modules\{Module}\Livewire\Forms;

use App\Modules\{Module}\Actions\{ActionName};
use App\Modules\{Module}\DTOs\{ActionName}Data;
use App\Modules\{Module}\Requests\{ActionName}Request;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class {Name}Form extends Component
{
    public string $field1 = '';
    public string $field2 = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return {ActionName}Request::rules();
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function submit({ActionName} ${actionName}): void
    {
        $validated = $this->validate();

        try {
            $data = new {ActionName}Data(
                field1: $validated['field1'],
                field2: $validated['field2'],
                remember: $validated['remember'] ?? false,
            );

            ${actionName}->execute($data);

            $this->redirectIntended(route('dashboard'));
        } catch (ValidationException $e) {
            $this->addError('field1', $e->getMessage());
        }
    }

    public function render()
    {
        return view('{module}::livewire.forms.{name}-form');
    }
}