<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid
{
    public readonly string $value;

    public function __construct(?string $uuid = null)
    {
        if ($uuid === null) {
            $this->value = RamseyUuid::uuid4()->toString();

            return;
        }

        if (! RamseyUuid::isValid($uuid)) {
            throw new InvalidArgumentException("Invalid UUID: {$uuid}");
        }

        $this->value = $uuid;
    }

    public static function generate(): self
    {
        return new self;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Uuid $other): bool
    {
        return $this->value === $other->value;
    }
}
