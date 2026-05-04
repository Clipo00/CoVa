<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Forms;

use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Exceptions\MaxVariablesReachedException;
use App\Modules\Organization\Models\Organization;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class BlueprintCreateForm extends Component
{
    public int $organizationId;
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public ?int $categoryId = null;
    public array $variables = [];

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categoryId' => ['nullable', 'integer', 'exists:categories,id'],
            'variables' => ['nullable', 'array'],
            'variables.*.key' => ['required', 'string', 'max:255'],
            'variables.*.type' => ['required', 'in:fixed,empty'],
            'variables.*.default_value' => ['nullable', 'string'],
            'variables.*.is_interactive' => ['boolean'],
            'variables.*.is_secret' => ['boolean'],
        ];
    }

    #[On('variables-updated')]
    public function updateVariables(array $variables): void
    {
        $this->variables = $variables;
    }

    public function updatedTitle(): void
    {
        $this->slug = \Illuminate\Support\Str::slug($this->title);
    }

    public function submit(CreateBlueprint $createBlueprint): void
    {
        $validated = $this->validate();
        $organization = Organization::findOrFail($this->organizationId);

        // Validar keys únicas
        $keys = array_column($this->variables, 'key');
        $keys = array_filter($keys);
        if (count($keys) !== count(array_unique($keys))) {
            $this->addError('variables', 'Las keys de las variables deben ser únicas.');
            return;
        }

        try {
            $blueprint = $createBlueprint->execute(
                organization: $organization,
                title: $validated['title'],
                slug: $validated['slug'],
                description: $validated['description'] ?: null,
                categoryId: $validated['categoryId'],
                tabsConfig: [],
                variables: $this->variables,
            );

            $this->redirect(route('blueprints.show', $blueprint->uuid));
        } catch (MaxBlueprintsReachedException $e) {
            $this->addError('title', $e->getMessage());
        } catch (MaxVariablesReachedException $e) {
            $this->addError('variables', $e->getMessage());
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
