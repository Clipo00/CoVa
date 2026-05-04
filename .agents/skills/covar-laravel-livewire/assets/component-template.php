<?php

declare(strict_types=1);

namespace App\Modules\{Module}\Livewire\Components;

use Livewire\Component;

class {Name} extends Component
{
    public string $text = '';
    public string $label = 'Copy';

    public function copy(): void
    {
        $this->dispatch('copy-to-clipboard', text: $this->text);
    }

    public function render()
    {
        return view('{module}::livewire.components.{name}');
    }
}