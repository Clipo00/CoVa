<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

class VueExpertSkill extends AbstractSkill
{
    protected function skillName(): string
    {
        return 'vue-expert';
    }

    protected function skillContent(): string
    {
        return <<<'MARKDOWN'
## Vue Expert

Follow Vue 3 best practices for production-quality applications:

### Composition API
- Always use `<script setup>` for components — it reduces boilerplate
- Use `ref()` for primitive values, `reactive()` for objects/arrays
- Prefer `computed()` over method calls for derived state (caching)
- Use `watch()` and `watchEffect()` for side effects — prefer `watchEffect` for auto-tracking
- Use `defineProps()` and `defineEmits()` with TypeScript generics for type safety
- Extract reusable logic into composables (`useAuth()`, `usePagination()`)

### Pinia Store Patterns
- Use Options API stores for simple CRUD, Setup stores for complex logic
- Access stores with `storeToRefs()` to preserve reactivity when destructuring
- Use actions for any logic that mutates state (even simple assignments)
- Keep stores flat — nest getters and actions by concern, not depth
- Use $patch for updating multiple properties simultaneously
- Use `onUnmounted()` in stores to clean up subscriptions and intervals

### Component Composition
- Keep components focused: one file = one component = one responsibility
- Use slots for flexible content injection: default slot, named slots, scoped slots
- Prefer emit-based communication over provide/inject for parent-child
- Use `defineAsyncComponent()` for code-splitting heavy components
- Use `<Teleport to="body">` for modals, tooltips, and overlays
- Use `<KeepAlive>` to preserve state in dynamic components

### Reactivity Patterns
- Never reassign a `reactive()` object — mutate its properties instead
- Use `shallowRef()` and `shallowReactive()` for large data structures
- Use `toRaw()` when passing reactive objects to non-reactive libraries
- Use `markRaw()` for objects that should never be made reactive (e.g., third-party instances)
- Use `triggerRef()` when mutating a `shallowRef`'s internal state
- Use `customRef()` for explicit dependency tracking control

### Testing Patterns
- Use `@vue/test-utils` with `mount()` for integration, `shallowMount()` for unit
- Use `vi.fn()` for mocking composables and external services
- Test user interactions via `wrapper.find('button').trigger('click')`
- Assert on rendered output, not internal component state
- Use `flushPromises()` for async updates before assertions
- Test composables in isolation with `@vue/test-utils` composable helpers
MARKDOWN;
    }
}
