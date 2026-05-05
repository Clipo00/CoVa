<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Presets;

class SOLIDPreset extends AbstractPreset
{
    protected function presetName(): string
    {
        return 'solid';
    }

    protected function presetContent(): string
    {
        return <<<'MARKDOWN'
## SOLID Principles

Apply SOLID principles throughout the codebase:

- **S**ingle Responsibility Principle (SRP)
  Each class has one reason to change. If a class does more than one job, split it.

- **O**pen/Closed Principle (OCP)
  Open for extension, closed for modification. Use inheritance or composition to extend behavior.

- **L**iskov Substitution Principle (LSP)
  Subtypes must be substitutable for their base types without altering program correctness.

- **I**nterface Segregation Principle (ISP)
  Many specific interfaces are better than one general interface. Clients shouldn't depend on methods they don't use.

- **D**ependency Inversion Principle (DIP)
  Depend on abstractions, not on concretions. High-level modules shouldn't depend on low-level modules.
MARKDOWN;
    }
}
