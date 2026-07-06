<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Tables;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use Livewire\Attributes\On;
use Livewire\Component;

class BlueprintList extends Component
{
    public string $search = '';

    public array $filters = [
        'organizations' => [],
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
        $this->filters = ['organizations' => []];
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

    public function getActiveFilterCountProperty(): int
    {
        return count($this->filters['organizations']);
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
                if (!empty($validIds)) {
                    $query->whereIn('organization_id', $validIds);
                }
            })
            ->when($this->publicOnly, function ($query) {
                $query->where('is_public', true);
            })
            ->with(['organization', 'tags'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('blueprint::livewire.tables.blueprint-list', [
            'blueprints' => $blueprints,
            'activeFilterCount' => $this->activeFilterCount,
        ]);
    }
}
