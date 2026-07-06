<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\DTOs;

/**
 * Configuration for MCP Servers tab.
 *
 * @param  McpServerEntry[]  $servers
 */
final class McpServersConfig
{
    /**
     * @param  McpServerEntry[]  $servers
     */
    public function __construct(
        public readonly array $servers = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $rawServers = $data['servers'] ?? [];

        if (!is_array($rawServers)) {
            return new self([]);
        }

        return new self(
            servers: array_map(
                fn (array $server) => McpServerEntry::fromArray($server),
                $rawServers,
            ),
        );
    }

    public function hasServers(): bool
    {
        return count($this->servers) > 0;
    }

    /**
     * @return string[]
     */
    public function toConfigArray(): array
    {
        return [
            'mcp_servers' => array_map(
                fn (McpServerEntry $server) => $server->toArray(),
                $this->servers,
            ),
        ];
    }
}
