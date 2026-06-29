<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Presets;

class TypeScriptStrictPreset extends AbstractPreset
{
    protected function presetName(): string
    {
        return 'typescript-strict';
    }

    protected function presetContent(): string
    {
        return <<<'MARKDOWN'
## TypeScript Strict Mode

### Configuration
- Enable `strict: true` in `tsconfig.json`
- Enable `noUncheckedIndexedAccess` for safe object access
- Enable `exactOptionalPropertyTypes` for precise optional handling
- Use `@ts-check` and JSDoc in plain JS files when migrating

### Best Practices
- Never use `any` — prefer `unknown` and narrow with type guards
- Use `const` assertions (`as const`) for literal types
- Prefer interfaces over type aliases for object shapes (extendable)
- Use `satisfies` operator for type validation without widening

### Type Inference
- Let TypeScript infer return types for simple functions
- Explicitly type function signatures (parameters and return)
- Use `ReturnType<T>` and `Parameters<T>` utility types over manual extraction
- Leverage `typeof` for type inference from runtime values

### Utility Types
- `Partial<T>`, `Required<T>`, `Readonly<T>` for type transformations
- `Pick<T, K>`, `Omit<T, K>` for subset types
- `Record<K, V>` for dictionary types
- `Awaited<T>` for promise unwrapping

### Discriminated Unions
- Use literal type properties as discriminators
- Exhaustive switch checks with `never` type
- Pattern matching with `switch(true)` for complex conditions

### Generics
- Use constraints (`extends`) on generic parameters
- Prefer generic functions over `any` casts
- Use conditional types for flexible APIs
MARKDOWN;
    }
}
