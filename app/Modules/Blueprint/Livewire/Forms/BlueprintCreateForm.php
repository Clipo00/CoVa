<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Forms;

use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Organization\Models\Organization;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class BlueprintCreateForm extends Component
{
    public int $organizationId;
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public ?int $categoryId = null;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categoryId' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    public function updatedTitle(): void
    {
        $this->slug = \Illuminate\Support\Str::slug($this->title);
    }

    public function submit(CreateBlueprint $createBlueprint): void
    {
        $validated = $this->validate();
        $organization = Organization::findOrFail($this->organizationId);

        try {
            $blueprint = $createBlueprint->execute(
                organization: $organization,
                title: $validated['title'],
                slug: $validated['slug'],
                description: $validated['description'] ?: null,
                categoryId: $validated['categoryId'],
                tabsConfig: [],
            );

            $this->redirect(route('blueprints.show', $blueprint->uuid));
        } catch (MaxBlueprintsReachedException $e) {
            $this->addError('title', $e->getMessage());
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
        return view('blueprint::livewire.forms.blueprint-create-form');
    }
}
