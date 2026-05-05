<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs;

use App\Modules\Blueprint\Contracts\TabInterface;
use App\Modules\Blueprint\DTOs\McpServersConfig;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;

class McpServersTab implements TabInterface
{
    public function type(): string
    {
        return TabType::MCP_SERVERS->value;
    }

    public function generate(array $config): TabOutput
    {
        $dto = McpServersConfig::fromArray($config);

        return new TabOutput(
            type: TabType::MCP_SERVERS,
            content: $dto->toConfigArray(),
        );
    }
}
