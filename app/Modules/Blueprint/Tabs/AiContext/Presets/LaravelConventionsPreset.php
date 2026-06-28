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

Follow Laravel conventions for consistent, maintainable code:

### Naming Conventions
- Models: Singular, PascalCase (`User`, `Organization`, `BlueprintVariable`)
- Controllers: Singular, PascalCase with `Controller` suffix (`UserController`)
- Actions: `{Verb}{Entity}` — `CreateBlueprint`, `AcceptInvitation`, `TransferFunds`
- Migrations: `YYYY_MM_DD_HHmmSS_create_{table}_table.php`
- Routes: kebab-case (`/blueprints/create`, `/organizations/{org}/members`)
- Table names: snake_case, plural (`blueprint_variables`, `organization_user`)
- Pivot tables: singular, alphabetical (`organization_user`, not `organization_has_user`)
- Form requests: Singular, PascalCase with `Request` suffix (`StoreBlueprintRequest`)

### Route Conventions
- Group routes by middleware and prefix
- Use `Route::resource()` for standard CRUD when applicable
- Name all routes with `{module}.{action}` pattern
- Keep routes in dedicated route files per module
- Use `slug` or `uuid` for route model binding, never auto-increment ID

### Controller Conventions
- Thin controllers: delegate business logic to Actions
- Controller methods follow RESTful naming: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- Always authorize with `$this->authorize('action', $model)` using Policies
- Return typed responses: `RedirectResponse`, `ViewResponse`, `JsonResponse`

### Action Pattern
- Each Action class has a single `execute()` method
- Actions are invokable classes registered in the container
- Actions receive dependencies via constructor injection
- Actions validate business rules and throw domain exceptions
- Actions DO NOT handle HTTP concerns (redirects, responses, sessions)

### Migration Conventions
- Always have both `up()` and `down()` methods
- Use `foreignId()` for foreign keys with proper constraints
- Define indexes for columns used in `WHERE`, `JOIN`, or `ORDER BY`
- Chain `cascadeOnDelete()` on foreign keys where appropriate
- Use `softDeletes()` for models that should support restoration

### Validation Conventions
- Use Form Requests for complex validation
- Keep validation rules in `rules()` method of Form Request
- Custom validation messages via `messages()` method
- Use `__()` for all user-facing validation messages
MARKDOWN;
    }
}
