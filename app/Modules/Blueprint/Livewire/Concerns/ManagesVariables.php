<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Concerns;

trait ManagesVariables
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

    public function cleanEmptyVariables(): void
    {
        $this->variables = array_values(array_filter($this->variables, fn($v) => !empty($v['key'])));
    }

    public function validateUniqueKeys(): bool
    {
        $keys = array_column($this->variables, 'key');
        if (count($keys) !== count(array_unique($keys))) {
            $this->addError('variables', 'Las keys de las variables deben ser únicas.');
            return false;
        }
        return true;
    }

    protected function variableRules(): array
    {
        return [
            'variables' => ['nullable', 'array'],
            'variables.*.key' => ['required', 'string', 'max:255'],
            'variables.*.type' => ['required', 'in:fixed,empty'],
            'variables.*.default_value' => ['nullable', 'string'],
            'variables.*.is_interactive' => ['boolean'],
            'variables.*.is_secret' => ['boolean'],
        ];
    }
}
