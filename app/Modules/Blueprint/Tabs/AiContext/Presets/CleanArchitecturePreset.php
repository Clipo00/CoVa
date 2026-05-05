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

Follow Clean Architecture principles:

### Layer Structure
- **Domain**: Business rules, entities, value objects, domain events
- **Application**: Use cases, actions, DTOs, interfaces (ports)
- **Infrastructure**: External services, database, file system (adapters)
- **Presentation**: HTTP controllers, CLI commands, views

### Key Rules
- Dependencies point inward only (Presentation → Application → Domain)
- Domain layer has NO external dependencies
- Application layer depends only on Domain and interfaces
- Infrastructure implements interfaces defined in Application
- Keep business logic in Domain, orchestration in Application

### Naming Conventions
- Actions for use cases: `{Verb}{Entity}` (e.g., `CreateBlueprint`, `TransferFunds`)
- DTOs for data transfer: `{Entity}{Data}` (e.g., `BlueprintData`)
- Value Objects are immutable and self-validating
- Interfaces describe what, not how: `TabInterface`, `PaymentGateway`
MARKDOWN;
    }
}
