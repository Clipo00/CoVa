<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Contracts;

interface AgentContentSegment
{
    /**
     * Unique identifier for this segment.
     */
    public function name(): string;

    /**
     * Markdown content for the agent.
     */
    public function content(): string;
}
