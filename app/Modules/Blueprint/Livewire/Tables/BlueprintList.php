<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Tables;

use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use Livewire\Component;

class BlueprintList extends Component
{
    public int $organizationId;
    public string $search = '';

    public function render()
    {
        $organization = Organization::findOrFail($this->organizationId);
        
        $blueprints = Blueprint::where('organization_id', $this->organizationId)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%");
            })
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('blueprint::livewire.tables.blueprint-list', [
            'blueprints' => $blueprints,
            'organization' => $organization,
        ]);
    }
}
