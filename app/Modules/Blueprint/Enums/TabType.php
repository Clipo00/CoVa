<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Enums;

enum TabType: string
{
    case VSCODE_EXTENSIONS = 'vscode_extensions';
    case MCP_SERVERS = 'mcp_servers';
    case AI_CONTEXT = 'ai_context';
    case SCRIPTS = 'scripts';

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

    /**
     * Get the translated display label for a tab type value.
     */
    public static function label(string $type): string
    {
        $key = match ($type) {
            self::VSCODE_EXTENSIONS->value => 'blueprint.tab_type_vscode',
            self::MCP_SERVERS->value => 'blueprint.tab_type_mcp',
            self::SCRIPTS->value => 'blueprint.tab_type_scripts',
            self::AI_CONTEXT->value => 'blueprint.tab_type_ai',
            default => null,
        };

        return $key ? __($key) : $type;
    }
}
