<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

use InvalidArgumentException;

/**
 * Represents a single post-install script command entry.
 */
final class ScriptEntry
{
    public function __construct(
        public readonly string $command,
        public readonly string $description = '',
        public readonly int $order = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        $command = $data['command'] ?? null;

        if (!is_string($command) || $command === '') {
            throw new InvalidArgumentException(__('blueprint.script_command_required'));
        }

        $order = (int) ($data['order'] ?? 0);

        if ($order < 0) {
            throw new InvalidArgumentException(__('blueprint.script_order_non_negative'));
        }

        return new self(
            command: $command,
            description: (string) ($data['description'] ?? ''),
            order: $order,
        );
    }

    public function toArray(): array
    {
        return [
            'command' => $this->command,
            'description' => $this->description,
            'order' => $this->order,
        ];
    }
}
