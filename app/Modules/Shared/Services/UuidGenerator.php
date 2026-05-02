<?php

declare(strict_types=1);

namespace App\Modules\Shared\Services;

use App\Modules\Shared\ValueObjects\Uuid;

class UuidGenerator
{
    public function generate(): Uuid
    {
        return Uuid::generate();
    }

    public function fromString(string $uuid): Uuid
    {
        return new Uuid($uuid);
    }
}
