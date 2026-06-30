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
        $this->variables = array_values(array_filter($this->variables, fn ($v) => ! empty($v['key'])));
    }

    /**
     * Move a variable up (-1) or down (+1) in the list.
     * Swaps the element with its neighbor. Does nothing at boundaries.
     */
    public function moveVariable(int $index, int $direction): void
    {
        $newIndex = $index + $direction;

        if ($newIndex < 0 || $newIndex >= count($this->variables)) {
            return;
        }

        $temp = $this->variables[$index];
        $this->variables[$index] = $this->variables[$newIndex];
        $this->variables[$newIndex] = $temp;

        $this->variables = array_values($this->variables);
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
            if (! $section) {
                continue;
            }

            // Respect user-chosen color; only auto-assign if empty
            $userColor = $variable['section_color'] ?? null;
            if ($userColor && $this->isValidHexColor($userColor)) {
                $sectionColors[$section] = $userColor;

                continue;
            }

            if (! isset($sectionColors[$section])) {
                $sectionColors[$section] = self::SECTION_COLORS[$colorIndex % count(self::SECTION_COLORS)];
                $colorIndex++;
            }
            $this->variables[$index]['section_color'] = $sectionColors[$section];
        }
    }

    /**
     * Hook de Livewire: asigna color automáticamente cuando cambia la sección
     * de una variable. Esto evita que el color picker aparezca con un paso de
     * retraso (el bug donde había que añadir otra variable para ver el color).
     */
    public function updatedVariables(mixed $value, string $key): void
    {
        if (! str_ends_with($key, '.section')) {
            return;
        }

        $index = (int) explode('.', $key)[0];
        $section = $this->variables[$index]['section'] ?? null;

        if (! $section) {
            return;
        }

        // Si ya tiene un color válido, no tocarlo (respeta elección del usuario)
        $existingColor = $this->variables[$index]['section_color'] ?? null;
        if ($existingColor && $this->isValidHexColor($existingColor)) {
            return;
        }

        // Reutilizar color de otra variable con la misma sección
        foreach ($this->variables as $i => $var) {
            if ($i === $index) {
                continue;
            }
            if (($var['section'] ?? null) === $section && ! empty($var['section_color'] ?? null)) {
                $this->variables[$index]['section_color'] = $var['section_color'];

                return;
            }
        }

        // Asignar un color nuevo del palette que no esté en uso
        $usedColors = [];
        foreach ($this->variables as $var) {
            if (! empty($var['section_color'] ?? null)) {
                $usedColors[] = $var['section_color'];
            }
        }

        $palette = self::SECTION_COLORS;
        $color = collect($palette)->first(fn ($c) => ! in_array($c, $usedColors)) ?? $palette[0];
        $this->variables[$index]['section_color'] = $color;
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
