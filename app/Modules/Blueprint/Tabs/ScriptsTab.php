<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs;

use App\Modules\Blueprint\Contracts\TabInterface;
use App\Modules\Blueprint\DTOs\ScriptsConfig;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;

class ScriptsTab implements TabInterface
{
    public function type(): string
    {
        return TabType::SCRIPTS->value;
    }

    public function generate(array $config): TabOutput
    {
        $dto = ScriptsConfig::fromArray($config);

        return new TabOutput(
            type: TabType::SCRIPTS,
            content: $dto->toOutputArray(),
        );
    }
}
