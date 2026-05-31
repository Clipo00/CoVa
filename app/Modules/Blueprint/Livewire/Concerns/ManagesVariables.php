<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Livewire\Concerns;

trait ManagesVariables
{
    public array $variables = [];

    public const SECTION_COLORS = [
        '#10b981', // emerald-500
        '#3b82f6', // blue-500
        '#f59e0b', // amber-500
        '#8b5cf6', // purple-500
        '#f43f5e', // rose-500
        '#06b6d4', // cyan-500
        '#f97316', // orange-500
        '#ec4899', // pink-500
        '#6366f1', // indigo-500
        '#14b8a6', // teal-500
    ];

    public function addVariable(): void
    {
        $this->variables[] = [
            'key' => '',
            'type' => 'fixed',
            'default_value' => '',
            'is_interactive' => false,
            'is_secret' => false,
            'section' => null,
            'section_color' => null,
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
            $this->addError('variables', __('blueprint.unique_variable_keys'));
            return false;
        }
        return true;
    }

    public function assignSectionColors(): void
    {
        $sectionColors = [];
        $colorIndex = 0;

        foreach ($this->variables as $index => $variable) {
            $section = $variable['section'] ?? null;
            if (!$section) {
                continue;
            }

            // Respect user-chosen color; only auto-assign if empty
            $userColor = $variable['section_color'] ?? null;
            if ($userColor && $this->isValidHexColor($userColor)) {
                $sectionColors[$section] = $userColor;
                continue;
            }

            if (!isset($sectionColors[$section])) {
                $sectionColors[$section] = self::SECTION_COLORS[$colorIndex % count(self::SECTION_COLORS)];
                $colorIndex++;
            }
            $this->variables[$index]['section_color'] = $sectionColors[$section];
        }
    }

    private function isValidHexColor(string $color): bool
    {
        return preg_match('/^#[a-fA-F0-9]{6}$/', $color) === 1;
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
            'variables.*.section' => ['nullable', 'string', 'max:255'],
            'variables.*.section_color' => ['nullable', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ];
    }
}
