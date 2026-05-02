<?php

declare(strict_types=1);

namespace App\Modules\Organization\Livewire\Tables;

use App\Modules\Auth\Models\User;
use Livewire\Component;

class OrganizationList extends Component
{
    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $organizations = $user->organizations()->with('owner')->get();

        return view('organization::livewire.tables.organization-list', [
            'organizations' => $organizations,
        ]);
    }
}
