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

### Composition API
- Use `<script setup>` for cleaner component code
- Prefer `ref()` for primitives and `reactive()` for objects
- Use `computed()` for derived state with explicit getter/setter
- Use `watch()` and `watchEffect()` for side effects
- Create composable functions for reusable stateful logic

### State Management with Pinia
- Define stores with `defineStore()` using composition API syntax
- Use `storeToRefs()` for reactive destructuring
- Keep actions async for API calls
- Use getters for computed store values
- Organize stores by domain (user store, cart store, etc.)

### Components
- Use single-file components (SFC) with `.vue` extension
- Follow the attribute order: directives, props, events
- Use `v-for` with `:key` for list rendering
- Use `v-if`/`v-else-if`/`v-else` for conditional rendering
- Prefer `defineProps` and `defineEmits` for component interfaces

### Reactivity
- Understand Vue's reactivity system (Proxy-based)
- Avoid deeply nested reactive objects — flatten when possible
- Use `shallowRef` and `shallowReactive` for large data sets
- Use `toRaw` to access the original object when needed
- Use `triggerRef` to manually trigger updates on `shallowRef`

### Testing
- Use Vitest for unit testing composables and stores
- Use Vue Test Utils + Vitest for component tests
- Use Cypress or Playwright for E2E testing
- Test component behavior through user interactions
MARKDOWN;
    }
}
