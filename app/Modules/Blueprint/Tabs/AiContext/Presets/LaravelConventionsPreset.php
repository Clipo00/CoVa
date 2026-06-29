<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Presets;

class LaravelConventionsPreset extends AbstractPreset
{
    protected function presetName(): string
    {
        return 'laravel-conventions';
    }

    protected function presetContent(): string
    {
        return <<<'MARKDOWN'
## Laravel Conventions

### Naming Conventions
- Controllers: singular PascalCase (e.g., `UserController`)
- Models: singular PascalCase (e.g., `User`, `Organization`)
- Migrations: snake_case with timestamp prefix
- Routes: kebab-case for URLs, camelCase for route names
- Tables: snake_case plural (e.g., `blueprint_variables`)
- Pivot tables: singular alphabetically sorted (e.g., `organization_user`)

### Routes and Controllers
- Use route model binding for automatic model resolution
- Group routes by middleware and prefix
- Use resource controllers for CRUD operations
- Keep controllers thin — delegate business logic to Actions

### Actions Pattern
- Each action is a single class with an `execute()` method
- Actions are invokable via `__invoke()` when appropriate
- Actions handle one specific use case
- Inject dependencies via constructor, not facades

### Validation
- Use FormRequest classes for controller validation
- Use `rules()` method for validation rules
- Custom validation rules as invokable classes
- Never trust `request()->all()` — always validate

### Migrations
- Each migration is a single change (create, modify, drop)
- Use `foreignId()` for foreign keys with cascade on delete
- Add indexes for frequently queried columns
- Use soft deletes with `SoftDeletes` trait

### Testing
- Feature tests for HTTP layer
- Unit tests for Actions, Policies, Models
- Use `RefreshDatabase` trait for isolation
- Test failure paths, not just happy paths
MARKDOWN;
    }
}
