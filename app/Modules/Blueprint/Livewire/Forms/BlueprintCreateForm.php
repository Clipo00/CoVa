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

    // Props pasadas desde el controller (serializables por Livewire)
    public ?int $preselectedOrg = null;
    public bool $lockOrganization = false;
    public array $userOrganizations = [];

    // Estado del formulario
    public ?int $organizationId = null;
    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public ?int $categoryId = null;
    public array $tabsConfig = [];

    public function mount(): void
    {
        // Validar que las organizaciones pasadas pertenecen realmente al usuario
        // (defensa en profundidad - el controller ya valido, pero Livewire puede ser manipulado)
        $this->validateUserOrganizations();

        if ($this->preselectedOrg !== null) {
            // Si viene preseleccionada, validar que esta en la lista permitida
            $allowedIds = array_column($this->userOrganizations, 'id');
            if (!in_array($this->preselectedOrg, $allowedIds, true)) {
                abort(403, __('blueprint.org_unauthorized'));
            }

            $this->organizationId = $this->preselectedOrg;
        } else {
            // Auto-seleccionar la primera org con cupo disponible
            $firstAvailable = collect($this->userOrganizations)
                ->firstWhere('hasAvailableSlots', true);

            $this->organizationId = $firstAvailable ? $firstAvailable['id'] : null;
        }

        $this->addVariable();
    }

    /**
     * Validar que las organizaciones en userOrganizations realmente pertenecen al usuario.
     * Esto previene tampering de props de Livewire.
     */
    private function validateUserOrganizations(): void
    {
        $user = auth()->user();
        $userOrgIds = $user->organizations()->pluck('organizations.id')->toArray();

        foreach ($this->userOrganizations as $org) {
            if (!isset($org['id']) || !in_array($org['id'], $userOrgIds, true)) {
                abort(403, __('blueprint.invalid_org_data'));
            }
        }
    }

    protected function rules(): array
    {
        return array_merge([
            'organizationId' => ['required', 'integer'],
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

        // SEGURIDAD: Validar que la organizacion seleccionada esta en la lista permitida
        $allowedIds = array_column($this->userOrganizations, 'id');
        if (!in_array($this->organizationId, $allowedIds, true)) {
            $this->addError('organizationId', __('blueprint.org_unauthorized'));
            return;
        }

        // SEGURIDAD: Validar que la org tiene cupo disponible
        $selectedOrgData = collect($this->userOrganizations)
            ->firstWhere('id', $this->organizationId);

        if (!$selectedOrgData || !$selectedOrgData['hasAvailableSlots']) {
            $this->addError('organizationId', __('blueprint.org_limit'));
            return;
        }

        $organization = Organization::findOrFail($this->organizationId);

        // SEGURIDAD: Validar permisos via Policy
        if (!auth()->user()->can('create', [Blueprint::class, $organization])) {
            $this->addError('title', __('blueprint.no_edit_permission'));
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
            $this->addError('organizationId', $e->getMessage());
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
