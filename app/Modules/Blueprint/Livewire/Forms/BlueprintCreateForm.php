<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Forms;

use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Exceptions\MaxVariablesReachedException;
use App\Modules\Blueprint\Livewire\Concerns\ManagesVariables;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class BlueprintCreateForm extends Component
{
    use ManagesVariables;

    public int $organizationId;
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public ?int $categoryId = null;
    public array $tabsConfig = [];

    public function mount(): void
    {
        $this->addVariable();
    }

    protected function rules(): array
    {
        return array_merge([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categoryId' => ['nullable', 'integer', 'exists:categories,id'],
            'tabsConfig' => ['nullable', 'array'],
        ], $this->variableRules());
    }

    public function updatingCategoryId($value): void
    {
        $this->categoryId = $value === '' ? null : $value;
    }

    public function updatedTitle(): void
    {
        $this->slug = \Illuminate\Support\Str::slug($this->title);
    }

    protected function getListeners(): array
    {
        return [
            'tabs-updated' => 'onTabsUpdated',
        ];
    }

    public function onTabsUpdated(array $tabs): void
    {
        $this->tabsConfig = $tabs;
    }

    public function submit(CreateBlueprint $createBlueprint): void
    {
        $this->categoryId = $this->categoryId === '' ? null : $this->categoryId;
        $this->cleanEmptyVariables();

        $validated = $this->validate();

        $organization = Organization::findOrFail($this->organizationId);

        if (!auth()->user()->can('create', [Blueprint::class, $organization])) {
            $this->addError('title', 'No tienes permisos para crear blueprints en esta organización.');
            return;
        }

        if (!$this->validateUniqueKeys()) {
            return;
        }

        // Convert tabsConfig to the format expected by tabs_config column
        $tabsForDb = array_values(array_map(fn($tab) => [
            'type' => $tab['type'],
            'config' => $tab['config'] ?? [],
        ], $this->tabsConfig));

        try {
            $blueprint = $createBlueprint->execute(
                organization: $organization,
                title: $validated['title'],
                slug: $validated['slug'],
                description: $validated['description'] ?: null,
                categoryId: $validated['categoryId'],
                tabsConfig: $tabsForDb,
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
        $categories = \App\Modules\Shared\Models\Category::all();
        return view('blueprint::livewire.forms.blueprint-create-form', compact('categories'));
    }
}