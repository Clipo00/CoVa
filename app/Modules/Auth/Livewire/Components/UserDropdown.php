<?php

declare(strict_types=1);

namespace App\Modules\Auth\Livewire\Components;

use Livewire\Component;

class UserDropdown extends Component
{
    public bool $open = false;

    protected function getListeners(): array
    {
        return [
            'profile-updated' => '$refresh',
        ];
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function render()
    {
        return view('auth::livewire.components.user-dropdown');
    }
}
