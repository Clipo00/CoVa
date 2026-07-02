<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext\Agents;

use App\Modules\Blueprint\Contracts\AgentContentSegment;
use App\Modules\Blueprint\Exceptions\UnknownSegmentException;

/**
 * Registry for agent templates.
 *
 * Similar to SegmentRegistry but specialized for agents
 * that compose skills.
 */
class AgentRegistry
{
    /**
     * @var array<string, AbstractAgent>
     */
    private array $agents = [];

    /**
     * Register an agent template.
     */
    public function register(AbstractAgent $agent): void
    {
        $this->agents[$agent->name()] = $agent;
    }

    /**
     * Get an agent by its name.
     *
     * @throws UnknownSegmentException
     */
    public function get(string $name): AbstractAgent
    {
        if (!isset($this->agents[$name])) {
            throw new UnknownSegmentException($name);
        }

        return $this->agents[$name];
    }

    /**
     * Check if an agent is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->agents[$name]);
    }

    /**
     * Get all registered agent names.
     *
     * @return string[]
     */
    public function names(): array
    {
        return array_keys($this->agents);
    }

    /**
     * Get all registered agents.
     *
     * @return AbstractAgent[]
     */
    public function all(): array
    {
        return $this->agents;
    }
}
