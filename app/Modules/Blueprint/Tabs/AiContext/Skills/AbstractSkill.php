<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Skills;

use App\Modules\Blueprint\Contracts\AgentContentSegment;

abstract class AbstractSkill implements AgentContentSegment
{
    /**
     * Each skill returns its name as the identifier.
     */
    abstract protected function skillName(): string;

    /**
     * Each skill returns its markdown content.
     */
    abstract protected function skillContent(): string;

    public function name(): string
    {
        return $this->skillName();
    }

    public function content(): string
    {
        return $this->skillContent();
    }
}
