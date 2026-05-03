<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Components;

use Livewire\Component;

class VariableManager extends Component
{
    public array $variables = [];

    public function addVariable(): void
    {
        $this->variables[] = [
            'key' => '',
            'type' => 'fixed',
            'default_value' => '',
            'is_interactive' => false,
            'is_secret' => false,
            'section' => null,
        ];
    }

    public function removeVariable(int $index): void
    {
        unset($this->variables[$index]);
        $this->variables = array_values($this->variables);
    }

    public function updatedVariables($value, $key): void
    {
        // Validar que no haya keys duplicadas
        $keys = array_column($this->variables, 'key');
        $keys = array_filter($keys);
        
        if (count($keys) !== count(array_unique($keys))) {
            $this->addError('variables', 'Las keys de las variables deben ser únicas.');
        }
    }

    public function mount(array $initialVariables = []): void
    {
        $this->variables = $initialVariables;
        
        if (empty($this->variables)) {
            $this->addVariable();
        }
    }

    public function render()
    {
        return view('blueprint::livewire.components.variable-manager');
    }
}
