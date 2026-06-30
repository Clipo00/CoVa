<?php

declare(strict_types=1);

namespace App\Modules\Shared\Services;

class JsonValidator
{
    public function isValid(string $json): bool
    {
        json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public function validate(string $json): void
    {
        if (!$this->isValid($json)) {
            throw new \InvalidArgumentException('Invalid JSON: '.json_last_error_msg());
        }
    }

    public function decode(string $json): array
    {
        $this->validate($json);
        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('JSON must decode to an array');
        }

        return $decoded;
    }

    public function encode(array $data): string
    {
        $json = json_encode($data);

        if ($json === false) {
            throw new \InvalidArgumentException('Failed to encode data to JSON');
        }

        return $json;
    }
}
