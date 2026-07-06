<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

class SOLIDSkill extends AbstractSkill
{
    protected function skillName(): string
    {
        return 'solid';
    }

    protected function skillContent(): string
    {
        return <<<'MARKDOWN'
## SOLID Principles

Apply SOLID principles throughout the codebase for maintainable, testable code:

### Single Responsibility Principle (SRP)
- Each class has exactly one reason to change
- If a class does more than one job, split it into separate classes
- Example: `UserService` that handles auth + email + billing → split into `AuthService`, `MailService`, `BillingService`
- In Laravel: Actions follow SRP — one `execute()` method, one concern
- DTOs follow SRP — they transport data, they don't validate or transform it

### Open/Closed Principle (OCP)
- Classes should be open for extension, closed for modification
- Use inheritance or composition to add behavior without changing existing code
- Use interfaces and polymorphism instead of conditionals (`if type == X`)
- Example: `TabInterface` allows adding new tab types without modifying existing tabs
- Use the Strategy pattern for interchangeable algorithms (payment gateways, exporters)
- Use the Decorator pattern for adding cross-cutting concerns (logging, caching)

### Liskov Substitution Principle (LSP)
- Subtypes must be substitutable for their base types without altering program correctness
- Child classes should not weaken parent class constraints (e.g., narrowing return types)
- Child classes should not strengthen preconditions (e.g., adding required params)
- Example: if a parent accepts `string|null`, a child should not require `string`
- Use composition over inheritance to avoid LSP violations
- Test with the base type, not the concrete type

### Interface Segregation Principle (ISP)
- Many specific interfaces are better than one general-purpose interface
- Clients should not depend on methods they don't use
- Example: instead of `UserInterface` with 20 methods, have `Authenticatable`, `HasProfile`, `Notifiable`
- Keep interfaces small (3-5 methods max) and focused on a single capability
- Split large interfaces using interface inheritance when decomposition is natural
- If a class cannot implement an interface meaningfully, the interface is too broad

### Dependency Inversion Principle (DIP)
- Depend on abstractions, not on concretions
- High-level modules should not depend on low-level modules — both should depend on abstractions
- Example: `PaymentService` depends on `PaymentGateway` interface, not on `StripeGateway` concretely
- In Laravel: bind interfaces to implementations in ServiceProviders
- Use constructor injection to receive dependencies, never create them inside the class
- Use the Service Container to wire dependencies automatically
- Use the Repository pattern to abstract data access behind interfaces (when beneficial)
MARKDOWN;
    }
}
