<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext;

use App\Modules\Blueprint\Contracts\AgentContentSegment;
use App\Modules\Blueprint\Exceptions\UnknownSegmentException;

/**
 * Registry for content segments (presets and skills).
 */
class SegmentRegistry
{
    /**
     * @var array<string, AgentContentSegment>
     */
    private array $segments = [];

    /**
     * Register a content segment.
     */
    public function register(AgentContentSegment $segment): void
    {
        $this->segments[$segment->name()] = $segment;
    }

    /**
     * Get a segment by its name.
     *
     * @throws UnknownSegmentException
     */
    public function get(string $name): AgentContentSegment
    {
        if (! isset($this->segments[$name])) {
            throw new UnknownSegmentException($name);
        }

        return $this->segments[$name];
    }

    /**
     * Check if a segment is registered.
     */
    public function has(string $name): bool
    {
        return isset($this->segments[$name]);
    }

    /**
     * Get all registered segment names.
     *
     * @return string[]
     */
    public function names(): array
    {
        return array_keys($this->segments);
    }

    /**
     * Get all registered segments.
     *
     * @return AgentContentSegment[]
     */
    public function all(): array
    {
        return $this->segments;
    }
}
