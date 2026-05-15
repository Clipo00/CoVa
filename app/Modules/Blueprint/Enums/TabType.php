<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Enums;

enum TabType: string
{
    case VSCODE_EXTENSIONS = 'vscode_extensions';
    case MCP_SERVERS = 'mcp_servers';
    case AI_CONTEXT = 'ai_context';

    /**
     * Get all tab type values.
     *
     * @return string[]
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if a string is a valid tab type.
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::values(), true);
    }
}
