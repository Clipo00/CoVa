<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

class TailwindSkill extends AbstractSkill
{
    protected function skillName(): string
    {
        return 'tailwind';
    }

    protected function skillContent(): string
    {
        return <<<'MARKDOWN'
## Tailwind CSS

When working with Tailwind CSS:

### Core Principles
- Use utility-first approach. Avoid custom CSS until necessary
- Use `@apply` only for repeated utility patterns
- Mobile-first: start with mobile styles, add responsive with prefixes

### Responsive Design
- `sm:` for 640px+
- `md:` for 768px+
- `lg:` for 1024px+
- `xl:` for 1280px+
- `2xl:` for 1536px+

### State Variants
- `hover:` for hover states
- `focus:` for focus states  
- `active:` for active/pressed states
- `disabled:` for disabled elements
- `dark:` for dark mode variants

### Best Practices
- Keep template readable: group related utilities logically
- Extract components for repeated patterns
- Use `clsx` or similar for conditional classes
- Consider custom theme config for brand-specific values
- Purge unused utilities in production
MARKDOWN;
    }
}
