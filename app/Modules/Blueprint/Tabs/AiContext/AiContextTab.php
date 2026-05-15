<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext;

use App\Modules\Blueprint\Contracts\AgentContentSegment;
use App\Modules\Blueprint\Contracts\TabInterface;
use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;

class AiContextTab implements TabInterface
{
    public function __construct(
        private readonly AgentGenerator $generator,
    ) {}

    public function type(): string
    {
        return TabType::AI_CONTEXT->value;
    }

    public function generate(array $config): TabOutput
    {
        $dto = AiContextConfig::fromArray($config);
        $markdown = $this->generator->generate($dto);

        return new TabOutput(
            type: TabType::AI_CONTEXT,
            content: $markdown,
            filename: 'agent.md',
        );
    }
}
