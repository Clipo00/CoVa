<?php

declare(strict_types=1);

namespace App\Modules\Organization\Livewire\Forms;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\DTOs\OrganizationData;
use App\Modules\Organization\Exceptions\MaxOrganizationsReachedException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CreateOrganizationForm extends Component
{
    public string $name = '';

    public string $slug = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:organizations,slug'],
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    public function updatedName(): void
    {
        $this->slug = Str::slug($this->name);
    }

    public function submit(CreateOrganization $createOrganization): void
    {
        $validated = $this->validate();

        try {
            /** @var User $user */
            $user = auth()->user();

            $data = new OrganizationData(
                name: $validated['name'],
                slug: $validated['slug'],
            );

            $organization = $createOrganization->execute(
                user: $user,
                name: $data->name,
                slug: $data->slug,
            );

            $this->redirect(route('organizations.show', $organization->slug));
        } catch (MaxOrganizationsReachedException $e) {
            $this->addError('name', $e->getMessage());
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
        return view('organization::livewire.forms.create-organization-form');
    }
}
