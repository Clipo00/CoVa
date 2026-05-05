<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Tables;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use Livewire\Component;

class BlueprintList extends Component
{
    public string $search = '';

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();

        $organizationIds = $user->organizations()->pluck('organizations.id');

        $blueprints = Blueprint::whereIn('organization_id', $organizationIds)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%");
            })
            ->with(['organization', 'category'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('blueprint::livewire.tables.blueprint-list', [
            'blueprints' => $blueprints,
        ]);
    }
}