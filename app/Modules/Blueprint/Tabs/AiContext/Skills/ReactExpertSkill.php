<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

class ReactExpertSkill extends AbstractSkill
{
    protected function skillName(): string
    {
        return 'react-expert';
    }

    protected function skillContent(): string
    {
        return <<<'MARKDOWN'
## React Expert

Follow React best practices for production-quality components:

### Hooks Rules
- Only call hooks at the top level (never inside conditions, loops, or nested functions)
- Only call hooks from React function components or custom hooks
- Use the `use` prefix for all custom hooks
- Keep hooks focused: one hook = one concern
- Use the exhaustive-deps ESLint rule to catch missing dependencies
- Prefix state setters with `set` (e.g., `setCount`, `setUser`)

### Composition Patterns
- Favor composition over inheritance — use `children` prop and render props
- Split UI from logic: use container/presentational pattern
- Use compound components for related components: `<Select><Select.Option /></Select>`
- Extract reusable logic into custom hooks, not HOCs or render props
- Use `React.memo()` only when profiling shows a performance bottleneck
- Prefer lifting state up over prop drilling with context

### State Management
- Keep state as local as possible: component state → context → external store
- Use `useReducer` for complex state with multiple sub-values
- Use context sparingly — too many consumers cause unnecessary re-renders
- Normalize nested state (like Redux-style entities) for complex data
- Derive computed values with `useMemo` and `useCallback` — measure first, optimize later
- Keep URL state in sync with `useSearchParams` for shareable views

### Performance Optimization
- Profile before optimizing — use React DevTools Profiler
- Use `React.lazy()` + `Suspense` for code-splitting routes
- Virtualize long lists with libraries like `react-window` or `tanstack-virtual`
- Avoid creating new objects/arrays in render (inline styles, callbacks)
- Use `useId()` for generating unique IDs accessible to all components
- Batch state updates — React 18 auto-batches in event handlers

### Testing Patterns
- Test behavior, not implementation (don't assert on internal state)
- Use `@testing-library/react` — query by role/text, not by test IDs
- Use `userEvent` over `fireEvent` for realistic interactions
- Test error states, loading states, and empty states — not just happy paths
- Keep tests co-located with components (`Button.test.tsx` next to `Button.tsx`)
- Mock at the network level (MSW), never mock child components
MARKDOWN;
    }
}
