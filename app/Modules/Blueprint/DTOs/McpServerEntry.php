<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

use InvalidArgumentException;

/**
 * Represents a single MCP server entry.
 */
final class McpServerEntry
{
    public function __construct(
        public readonly string $name,
        public readonly string $command,
        public readonly array $args = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $name = $data['name'] ?? null;
        $command = $data['command'] ?? null;

        if (!is_string($name) || $name === '') {
            throw new InvalidArgumentException('MCP server must have a string "name".');
        }

        if (!is_string($command) || $command === '') {
            throw new InvalidArgumentException("MCP server '{$name}' must have a string 'command'.");
        }

        return new self(
            name: $name,
            command: $command,
            args: self::filterArgs($data['args'] ?? []),
        );
    }

    /**
     * @param  mixed[]  $args
     * @return string[]
     */
    private static function filterArgs(array $args): array
    {
        return array_map(fn ($arg) => (string) $arg, $args);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'command' => $this->command,
            'args' => $this->args,
        ];
    }

    public function buildCommand(): string
    {
        $cmd = $this->command;

        if (!empty($this->args)) {
            $cmd .= ' '.implode(' ', array_map(fn ($arg) => escapeshellarg($arg), $this->args));
        }

        return $cmd;
    }
}
