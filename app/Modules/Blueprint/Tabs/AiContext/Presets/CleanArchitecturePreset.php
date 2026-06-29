<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Presets;

class CleanArchitecturePreset extends AbstractPreset
{
    protected function presetName(): string
    {
        return 'clean-architecture';
    }

    protected function presetContent(): string
    {
        return <<<'MARKDOWN'
## Clean Architecture

Follow Clean Architecture principles for maintainable, testable, and framework-independent code:

### Layer Structure
- **Domain**: Business rules, entities, value objects, domain events, repository interfaces
  - Has ZERO dependencies on frameworks, databases, or external libraries
  - Contains the core business logic that makes the application what it is
  - Value Objects are immutable and self-validating in their constructors
  - Domain Events capture meaningful business occurrences (e.g., `BlueprintCreated`, `MemberJoined`)
  
- **Application**: Use cases (Actions), DTOs, port interfaces
  - Orchestrates the flow of data between domain and infrastructure
  - Depends only on Domain layer and interfaces defined here
  - Each use case (Action) has exactly one public `execute()` method
  - DTOs are simple data carriers with `fromArray()`/`toArray()` methods
  - Ports define interfaces for external communication (e.g., `PaymentGateway`, `MailSender`)

- **Infrastructure**: Database implementations, external service adapters, file system
  - Implements the interfaces (ports) defined in the Application layer
  - Contains Eloquent models, API clients, queue jobs, cache implementations
  - May depend on frameworks (Laravel) and external libraries
  - Repository implementations translate between Eloquent models and Domain entities

- **Presentation**: HTTP controllers, CLI commands, Blade views, Livewire components, API resources
  - Handles HTTP concerns only — input validation, response formatting, session management
  - Delegates all business logic to Application layer (Actions)
  - Never contains business rules or database queries directly

### Key Rules
- Dependencies point inward only: Presentation → Application → Domain
- Outer layers can depend on inner layers, but NEVER the reverse
- Domain layer entities have NO external dependencies (no Laravel, no DB)
- Application layer depends only on Domain abstractions and its own interfaces
- Infrastructure implements interfaces defined in Application layer
- Keep business logic in Domain entities/value objects, orchestration in Application Actions
- Cross-cutting concerns (logging, caching, auth) are handled at the boundary via decorators/middleware

### Naming Conventions
- Actions for use cases: `{Verb}{Entity}` — `CreateBlueprint`, `TransferFunds`, `ResolveBlueprint`
- DTOs for data transfer: `{Entity}{Data}` — `BlueprintData`, `VariableData`, `OrganizationData`
- Value Objects describe WHAT they are: `Email`, `Slug`, `Uuid`, `Money`, `Percentage`
- Interfaces describe what, not how: `TabInterface`, `PaymentGateway`, `UserRepository`
- Adapters/Implementations include the technology: `PostgresUserRepository`, `StripePaymentGateway`

### Testing Strategy
- Domain layer: pure unit tests — no database, no framework, no mocking
- Application layer: unit tests with mocked port interfaces
- Infrastructure layer: integration tests with test database (SQLite in-memory)
- Presentation layer: feature tests that exercise the full HTTP stack
- Follow the Testing Trophy: lots of integration tests, some unit tests, few end-to-end
MARKDOWN;
    }
}
