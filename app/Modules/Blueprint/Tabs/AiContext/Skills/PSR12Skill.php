<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

class PSR12Skill extends AbstractSkill
{
    protected function skillName(): string
    {
        return 'psr12';
    }

    protected function skillContent(): string
    {
        return <<<'MARKDOWN'
## PSR-12 Coding Standard

Follow PSR-12 coding standard for all PHP code:

### Formatting
- Use 4 spaces for indentation (no tabs)
- Line length SHOULD be 80 characters max, MAY be 120 characters
- Opening braces for classes/methods/interfaces/traits on their own line
- Control structure opening brace on same line, closing on own line
- Single blank line between methods and logical sections
- Visibility MUST be declared on ALL properties and methods (`public`, `protected`, `private`)
- Property and method names MUST NOT be prefixed with underscore (legacy PHP 4 convention)

### Syntax Rules
- Use `elseif` instead of `else if` (single word)
- Control structure keywords MUST have one space after them
- Method calls MUST NOT have a space before the opening parenthesis
- Argument lists MAY be split across multiple lines, with each argument on its own line
- Use `declare(strict_types=1);` as the first declaration in every PHP file
- Use typed properties wherever possible: `public string $name` not `public $name`
- Use union types and mixed types where appropriate (PHP 8.0+)

### Namespace and Imports
- One namespace per file
- One `use` statement per import
- `use` statements MUST be alphabetically ordered
- Group `use` statements: PHP classes, then vendor packages, then app classes
- Never import classes from the same namespace (they are implicitly available)

### PHP Tags and Files
- Use `<?php` or short echo tag `<?=`, never use other PHP tags
- Files MUST end with a single blank line (no trailing `?>` PHP close tag)
- For pure PHP files (no HTML), the closing `?>` tag MUST be omitted
- Files MUST use UTF-8 without BOM encoding

### Best Practices
- Use type hints for all function parameters and return types
- Use `match` expressions (PHP 8.0+) over `switch` for value matching
- Use named arguments sparingly — prefer when arguments are self-documenting
- Use readonly properties for DTOs and immutable data objects
- Use constructor property promotion to reduce boilerplate
- Use `enum` instead of class constants for fixed value sets (PHP 8.1+)
- Use `array_filter`, `array_map`, `array_reduce` over foreach loops for data transformations
MARKDOWN;
    }
}
