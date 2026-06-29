<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext;

use App\Modules\Blueprint\Contracts\AgentContentSegment;
use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\DTOs\AiContextSegment;

class AgentGenerator
{
    public function __construct(
        private readonly SegmentRegistry $presets,
        private readonly SegmentRegistry $skills,
    ) {}

    /**
     * Generate agent.md content from AI Context config.
     *
     * Iterates segments in order, resolving content with precedence:
     * segment override > registry default > empty.
     */
    public function generate(AiContextConfig $config): string
    {
        if ($config->isEmpty()) {
            return '';
        }

        $sections = ["# Agent Context"];

        foreach ($config->segments as $segment) {
            $content = $this->resolveContent($segment);

            if ($content === null) {
                continue;
            }

            $sections[] = $content;
        }

        return implode("\n\n---\n\n", $sections);
    }

    /**
     * Resolve markdown content for a single segment.
     *
     * Precedence: segment override > registry default > null (skip).
     */
    private function resolveContent(AiContextSegment $segment): ?string
    {
        // Override content or custom segment: use provided content with generated heading
        if ($segment->content !== null) {
            $heading = "## {$segment->name}";

            if ($segment->content === '') {
                return $heading;
            }

            return "{$heading}\n\n{$segment->content}";
        }

        // Registry content (preset/skill with null content)
        $registry = null;
        if ($segment->isPreset()) {
            $registry = $this->presets;
        } elseif ($segment->isSkill()) {
            $registry = $this->skills;
        }

        if ($registry !== null && $registry->has($segment->name)) {
            return $registry->get($segment->name)->content();
        }

        // Segment not found in registry and no override — skip
        return null;
    }

    /**
     * Get all available preset names.
     *
     * @return string[]
     */
    public function presetNames(): array
    {
        return $this->presets->names();
    }

    /**
     * Get all available skill names.
     *
     * @return string[]
     */
    public function skillNames(): array
    {
        return $this->skills->names();
    }
}
