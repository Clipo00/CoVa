<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Forms;

use App\Modules\Blueprint\Actions\UpdateBlueprint;
use App\Modules\Blueprint\Livewire\Concerns\ManagesVariables;
use App\Modules\Blueprint\Models\Blueprint;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class BlueprintEditForm extends Component
{
    use ManagesVariables;

    public Blueprint $blueprint;
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public ?int $categoryId = null;
    public array $tabsConfig = [];

    public function mount(Blueprint $blueprint): void
    {
        $this->blueprint = $blueprint;
        $this->title = $blueprint->title;
        $this->slug = $blueprint->slug;
        $this->description = $blueprint->description ?? '';
        $this->categoryId = $blueprint->category_id;

        // Ensure tabs_config is a proper array (not null)
        $raw = $blueprint->tabs_config;
        $this->tabsConfig = is_array($raw) ? $raw : [];

        $this->variables = $blueprint->variables->map(function ($variable) {
            return [
                'key' => $variable->key,
                'type' => $variable->type,
                'default_value' => $variable->default_value ?? '',
                'is_interactive' => (bool) $variable->is_interactive,
                'is_secret' => (bool) $variable->is_secret,
                'section' => $variable->section,
            ];
        })->toArray();

        if (empty($this->variables)) {
            $this->addVariable();
        }
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

    /**
     * Sync tabs from TabManager child component.
     * In Livewire 3, dispatched events from child components are
     * received via the #[On] attribute or getListeners().
     */
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

    public function updatingCategoryId($value): void
    {
        $this->categoryId = $value === '' ? null : $value;
    }

    public function updatedTitle(): void
    {
        $this->slug = \Illuminate\Support\Str::slug($this->title);
    }

    public function submit(UpdateBlueprint $updateBlueprint): void
    {
        $this->categoryId = $this->categoryId === '' ? null : $this->categoryId;
        $this->cleanEmptyVariables();

        $validated = $this->validate();

        if (!auth()->user()->can('update', $this->blueprint)) {
            $this->addError('title', __('blueprint.no_edit_permission'));
            return;
        }

        if (!$this->validateUniqueKeys()) {
            return;
        }

        try {
            // Normalize tabsConfig: ensure each tab has type and config
            $tabsForDb = array_values(array_map(fn($tab) => [
                'type' => $tab['type'],
                'config' => $tab['config'] ?? [],
            ], $this->tabsConfig));

            $updateBlueprint->execute(
                blueprint: $this->blueprint,
                data: [
                    'title' => $validated['title'],
                    'slug' => $validated['slug'],
                    'description' => $validated['description'] ?: null,
                    'category_id' => $validated['categoryId'],
                    'tabs_config' => $tabsForDb,
                ],
                variables: $this->variables,
            );

            $this->redirect(route('blueprints.show', $this->blueprint->uuid));
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
        return view('blueprint::livewire.forms.blueprint-edit-form', compact('categories'));
    }
}