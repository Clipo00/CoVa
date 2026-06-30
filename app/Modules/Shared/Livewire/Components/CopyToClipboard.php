<?php

declare(strict_types=1);

namespace App\Modules\Shared\Livewire\Components;

use Livewire\Component;

class CopyToClipboard extends Component
{
    public string $text;

    public string $label = '';

    public ?string $successMessage = null;

    public function mount(string $text, string $label = '', ?string $successMessage = null): void
    {
        $this->text = $text;
        $this->label = $label;
        $this->successMessage = $successMessage ?? __('shared.copied');
    }

    public function copy(): void
    {
        $this->dispatch('copy-to-clipboard', text: $this->text);
        $this->dispatch('notify', message: $this->successMessage);
    }

    public function render()
    {
        return view('shared::livewire.components.copy-to-clipboard');
    }
}
