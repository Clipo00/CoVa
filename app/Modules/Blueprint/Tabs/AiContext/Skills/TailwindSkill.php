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

When working with Tailwind CSS, follow these patterns:

### Core Principles
- Use utility-first approach — compose styles from single-purpose utility classes
- Avoid writing custom CSS until a clear, repeated pattern emerges (3+ repetitions)
- Use `@apply` ONLY for truly repeated utility combinations, not as a CSS abstraction layer
- Configuration over convention — customize everything in `tailwind.config.js`
- Use design tokens: define colors, spacing, fonts in config so utilities are consistent
- Run `npx tailwindcss init` to generate a config file with all customization options

### Responsive Design
- Mobile-first: start with base (mobile) styles, add responsive variants with breakpoint prefixes
- Breakpoints: `sm:` (640px+), `md:` (768px+), `lg:` (1024px+), `xl:` (1280px+), `2xl:` (1536px+)
- Example: `text-sm sm:text-base lg:text-lg` — scales text across breakpoints
- Use container queries for component-level responsiveness (Tailwind v3.2+)
- Use `max-w-*` and `w-full` combination for fluid layouts
- Use CSS Grid (`grid-cols-1 md:grid-cols-2 lg:grid-cols-3`) for responsive grids

### State Variants
- `hover:` — hover states for links, buttons, cards
- `focus:` and `focus-visible:` — focus states (use `focus-visible` for keyboard-only focus ring)
- `active:` — pressed state for buttons and interactive elements
- `disabled:` — disabled form elements and buttons
- `group-hover:` — style a child when parent is hovered (group pattern)
- `peer:` and `peer-*:` — style siblings based on a peer element state (checkbox + label)
- `dark:` — dark mode variants (requires `darkMode: 'class'` in config)
- `motion-safe:` and `motion-reduce:` — respect user motion preferences

### Best Practices
- Keep template readable: group related utilities logically (layout → spacing → typography → colors)
- Extract reusable UI patterns as Vue/React components (e.g., `<Button>`, `<Card>`, `<Badge>`)
- Use `clsx` or `tailwind-merge` for conditional classes and merging
- Use `@tailwindcss/typography` plugin for prose styling (markdown content)
- Use `@tailwindcss/forms` plugin for consistent form element styling
- Purge unused utilities via `content` configuration in `tailwind.config.js`
- Use JIT mode (default in v3+) for fast builds and small CSS output

### Design System Integration
- Define brand colors under `theme.extend.colors` in config
- Use consistent spacing scale: `p-4` (16px), `gap-6` (24px), `space-y-3` (12px)
- Create reusable animation keyframes in `theme.extend.animation`
- Use `@layer` directive to organize custom styles: `@layer base { ... }`, `@layer components { ... }`
- Document design decisions: if you customize a utility, note why in the config or a comment
- Use CSS variables for runtime-themeable values: `text-[var(--color-primary)]`
MARKDOWN;
    }
}
