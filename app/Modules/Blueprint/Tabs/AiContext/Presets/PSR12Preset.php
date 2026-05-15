<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Presets;

class PSR12Preset extends AbstractPreset
{
    protected function presetName(): string
    {
        return 'psr12';
    }

    protected function presetContent(): string
    {
        return <<<'MARKDOWN'
## PSR-12 Coding Standard

Follow PSR-12 coding standard for all PHP code:

- Use 4 spaces for indentation (no tabs)
- Opening braces for classes/methods on their own line
- Visibility MUST be declared on all properties and methods
- Property and method names MUST NOT be prefixed with underscore
- Control structure keywords must have one space before opening parenthesis
- Use `elseif` instead of `else if`
- Opening parenthesis for control structures on same line, closing on own line
- Use `phpcs:disable` and `phpcs:enable` annotations sparingly
- Constants must be declared in uppercase with underscore separators
MARKDOWN;
    }
}
