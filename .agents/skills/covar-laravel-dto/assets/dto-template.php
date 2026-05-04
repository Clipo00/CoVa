<?php

declare(strict_types=1);

namespace App\Modules\{Module}\DTOs;

readonly class {Name}Data
{
    public function __construct(
        // Propiedades del DTO
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            // Mapeo de datos
        );
    }
}