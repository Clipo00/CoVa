<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

class TypeScriptStrictSkill extends AbstractSkill
{
    protected function skillName(): string
    {
        return 'typescript-strict';
    }

    protected function skillContent(): string
    {
        return <<<'MARKDOWN'
## TypeScript Strict Mode

Follow strict TypeScript conventions for type-safe, maintainable code:

### Strict Mode Configuration
- Enable `strict: true` in `tsconfig.json` (enables all strict flags)
- Never disable `noImplicitAny`, `strictNullChecks`, `strictFunctionTypes`, or `strictBindCallApply`
- Enable `noUncheckedIndexedAccess` for safer object access
- Enable `exactOptionalPropertyTypes` for precise optional handling
- Use `verbatimModuleSyntax` for consistent module imports

### The `any` Prohibition
- Never use `any` type — it defeats type checking entirely
- Use `unknown` instead of `any` when the type is truly unknown
- Use `never` for unreachable code branches
- Use type narrowing with `typeof`, `instanceof`, or custom type guards
- Prefer `Record<string, T>` over index signatures with `any`

### Const Assertions
- Use `as const` for literal types and immutable arrays
- Use `const` assertions on objects to get deeply readonly types
- Prefer `readonly` arrays (`readonly T[]` or `ReadonlyArray<T>`) over mutable ones
- Use `as const` with `Object.freeze()` for runtime + compile-time immutability

### Type Inference Best Practices
- Let TypeScript infer return types of simple functions
- Explicitly type function signatures (parameters and return) for public APIs
- Use `satisfies` operator (TS 4.9+) to validate types without widening
- Prefer `interface` for public API contracts, `type` for unions and utility types
- Use `Pick`, `Omit`, `Partial`, `Required` for derived types

### Utility Types and Patterns
- Use `ReturnType<T>` and `Parameters<T>` for function type extraction
- Use `Awaited<T>` for promise unwrapping
- Use `NonNullable<T>` to remove `null | undefined` from unions
- Model state machines with discriminated unions: `type State = { status: 'loading' } | { status: 'success'; data: T } | { status: 'error'; error: Error }`
- Use `satisfies` to validate object shapes without losing literal types

### Code Quality Rules
- No unused variables (`noUnusedLocals`, `noUnusedParameters`)
- No implicit returns in void functions
- Prefer `for...of` over indexed `for` loops
- Use `Array.isArray()` for type-safe array checks
- Use optional chaining (`?.`) and nullish coalescing (`??`) instead of `&&` chains
MARKDOWN;
    }
}
