<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use InvalidArgumentException;

class {Name}
{
    public readonly string $value;

    public function __construct(string $value)
    {
        // Validación
        if (!$this->isValid($value)) {
            throw new InvalidArgumentException("Invalid {name}: {$value}");
        }

        // Normalización (si aplica)
        $this->value = $this->normalize($value);
    }

    protected function isValid(string $value): bool
    {
        // Implementar validación
        return true;
    }

    protected function normalize(string $value): string
    {
        // Implementar normalización
        return $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals({Name} $other): bool
    {
        return $this->value === $other->value;
    }
}