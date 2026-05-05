<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs;

use App\Modules\Blueprint\Contracts\TabInterface;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\DTOs\VscodeExtensionsConfig;
use App\Modules\Blueprint\Enums\TabType;

class VscodeExtensionsTab implements TabInterface
{
    public function type(): string
    {
        return TabType::VSCODE_EXTENSIONS->value;
    }

    public function generate(array $config): TabOutput
    {
        $dto = VscodeExtensionsConfig::fromArray($config);

        return new TabOutput(
            type: TabType::VSCODE_EXTENSIONS,
            content: [
                'extensions' => $dto->extensions,
                'install_command' => $dto->installCommand(),
            ],
        );
    }
}
