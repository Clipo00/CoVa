<?php

declare(strict_types=1);

namespace App\Modules\{Module}\Livewire\Tables;

use Livewire\Component;
use Livewire\WithPagination;

class {Name}List extends Component
{
    use WithPagination;

    public Organization $organization;
    public string $search = '';
    public string $sortField = 'title';
    public bool $sortAsc = true;

    public function get{Model}ListProperty()
    {
        return $this->organization->{models}()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortAsc ? 'asc' : 'desc')
            ->paginate(12);
    }

    public function render()
    {
        return view('{module}::livewire.tables.{name}-list', [
            '{models}' => $this->{model}List,
        ]);
    }
}