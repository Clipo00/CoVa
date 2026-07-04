<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Livewire;

use App\Models\Tag;
use App\Modules\Blueprint\Models\Blueprint;
use Livewire\Component;
use Livewire\WithPagination;

class MarketplaceList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sort = 'recent';

    public array $selectedTags = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'recent'],
        'selectedTags' => ['except' => []],
    ];

    public function getBlueprintsProperty()
    {
        return Blueprint::whereHas('organization', fn ($q) => $q->where('slug', 'covar-marketplace'))
            ->where('is_public', true)
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->selectedTags, fn ($q) => $q->whereHas('tags', fn ($t) => $t->whereIn('name', $this->selectedTags)))
            ->when($this->sort === 'rating', fn ($q) => $q->orderBy('votes_count', 'desc'))
            ->when($this->sort === 'subscribers', fn ($q) => $q->orderBy('subscribers_count', 'desc'))
            ->when($this->sort === 'recent', fn ($q) => $q->orderBy('created_at', 'desc'))
            ->with(['tags', 'organization'])
            ->paginate(20);
    }

    public function getAvailableTagsProperty()
    {
        return Tag::select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
    }

    public function toggleTag(string $tag): void
    {
        if (in_array($tag, $this->selectedTags, true)) {
            $this->selectedTags = array_values(array_filter($this->selectedTags, fn ($t) => $t !== $tag));
        } else {
            $this->selectedTags[] = $tag;
        }

        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('marketplace::livewire.marketplace-list', [
            'blueprints' => $this->blueprints,
            'availableTags' => $this->availableTags,
        ]);
    }
}
