<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Tables;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Shared\Models\Category;
use Livewire\Attributes\On;
use Livewire\Component;

class BlueprintList extends Component
{
    public string $search = '';

    public array $filters = [
        'organizations' => [],
        'categories' => [],
    ];

    public bool $showFilters = false;

    public bool $preserveFilters = false;

    public bool $publicOnly = false;

    // ──────────────────────────────────────────────
    //  Event Listeners
    // ──────────────────────────────────────────────

    #[On('restoreFilters')]
    public function restoreFilters(array $filters): void
    {
        $this->filters = $filters;
        $this->preserveFilters = true;
    }

    // ──────────────────────────────────────────────
    //  Actions
    // ──────────────────────────────────────────────

    public function removeFilter(string $type, int $value): void
    {
        $this->filters[$type] = array_values(
            array_filter($this->filters[$type], fn (int $id) => $id !== $value)
        );

        if ($this->preserveFilters) {
            $this->dispatch('persist-filters');
        }
    }

    public function clearFilters(): void
    {
        $this->filters = ['organizations' => [], 'categories' => []];
        $this->showFilters = false;

        if ($this->preserveFilters) {
            $this->dispatch('persist-filters');
        }
    }

    // ──────────────────────────────────────────────
    //  Hooks
    // ──────────────────────────────────────────────

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'filters') && $this->preserveFilters) {
            $this->dispatch('persist-filters');
        }
    }

    // ──────────────────────────────────────────────
    //  Computed (lazy via method for render data)
    // ──────────────────────────────────────────────

    public function getUserOrganizationsProperty()
    {
        return auth()->user()->organizations()
            ->select('organizations.id', 'organizations.name')
            ->orderBy('organizations.name')
            ->get();
    }

    public function getCategoriesProperty()
    {
        $userOrgIds = auth()->user()->organizations()->pluck('organizations.id');

        return Category::select('id', 'name')
            ->whereHas('blueprints', function ($query) use ($userOrgIds) {
                $query->whereIn('organization_id', $userOrgIds);
            })
            ->orderBy('name')
            ->get();
    }

    public function getActiveFilterCountProperty(): int
    {
        return count($this->filters['organizations']) + count($this->filters['categories']);
    }

    // ──────────────────────────────────────────────
    //  Render
    // ──────────────────────────────────────────────

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        $organizationIds = $user->organizations()->pluck('organizations.id');

        $blueprints = Blueprint::whereIn('organization_id', $organizationIds)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%");
            })
            ->when($this->filters['organizations'], function ($query) use ($organizationIds) {
                // Security: only filter by orgs the user actually belongs to
                $validIds = array_intersect(
                    $this->filters['organizations'],
                    $organizationIds->toArray()
                );
                if (! empty($validIds)) {
                    $query->whereIn('organization_id', $validIds);
                }
            })
            ->when($this->filters['categories'], function ($query) {
                $query->whereIn('category_id', $this->filters['categories']);
            })
            ->when($this->publicOnly, function ($query) {
                $query->where('is_public', true);
            })
            ->with(['organization', 'category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('blueprint::livewire.tables.blueprint-list', [
            'blueprints' => $blueprints,
            'activeFilterCount' => $this->activeFilterCount,
        ]);
    }
}
