<?php

declare(strict_types=1);

namespace App\Modules\Shared\ValueObjects;

use InvalidArgumentException;

class Slug
{
    public readonly string $value;

    public function __construct(string $slug)
    {
        $sanitized = $this->sanitize($slug);

        if (empty($sanitized)) {
            throw new InvalidArgumentException('Slug cannot be empty after sanitization');
        }

        if (! preg_match('/^[a-z0-9-]+$/', $sanitized)) {
            throw new InvalidArgumentException("Invalid slug format: {$sanitized}. Only lowercase letters, numbers, and hyphens are allowed.");
        }

        $this->value = $sanitized;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Slug $other): bool
    {
        return $this->value === $other->value;
    }

    private function sanitize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9-]/', '-', $value);
        $value = preg_replace('/-+/', '-', $value);

        return trim($value, '-');
    }
}
