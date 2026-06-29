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

### Hooks
- Use `useState` for local component state
- Use `useEffect` for side effects with proper dependency arrays
- Use `useCallback` and `useMemo` for performance optimization
- Create custom hooks for reusable stateful logic
- Follow the Rules of Hooks: call at top level, only in React functions

### Composition Patterns
- Prefer composition over inheritance
- Use `children` prop for flexible component layouts
- Use render props and function-as-children patterns sparingly
- Leverage React Context for global state (auth, theme, locale)

### State Management
- Lift state up when multiple components need shared state
- Use `useReducer` for complex state logic
- Consider Zustand or Jotai for mid-complexity state management
- Keep URL state in sync with navigation (search params, filters)

### Performance
- Memoize expensive computations with `useMemo`
- Prevent unnecessary re-renders with `React.memo`
- Virtualize long lists with `react-window` or `tanstack-virtual`
- Code-split with `React.lazy` and `Suspense`
- Use `useTransition` for non-urgent state updates

### Testing
- Test component behavior, not implementation
- Use React Testing Library for user-centric tests
- Use `@testing-library/user-event` over `fireEvent`
- Mock external services with MSW (Mock Service Worker)
MARKDOWN;
    }
}
