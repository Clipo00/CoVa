<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Agents;

use App\Modules\Blueprint\Models\AgentTemplate;

/**
 * Agent template that reads its configuration from a database row.
 *
 * Delegates agentName(), agentContent(), and agentSkills() to
 * an AgentTemplate Eloquent model, preserving the hasRouter()
 * and resolveWithSkills() behavior inherited from AbstractAgent.
 */
class DatabaseAgent extends AbstractAgent
{
    public function __construct(
        private readonly AgentTemplate $template,
    ) {}

    protected function agentName(): string
    {
        return $this->template->name;
    }

    protected function agentContent(): string
    {
        return $this->template->content;
    }

    protected function agentSkills(): array
    {
        return $this->template->skills ?? [];
    }
}
