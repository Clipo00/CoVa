<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext;

use App\Modules\Blueprint\Contracts\AgentContentSegment;
use App\Modules\Blueprint\DTOs\AiContextConfig;

class AgentGenerator
{
    public function __construct(
        private readonly SegmentRegistry $presets,
        private readonly SegmentRegistry $skills,
    ) {}

    /**
     * Generate agent.md content from AI Context config.
     */
    public function generate(AiContextConfig $config): string
    {
        if ($config->isEmpty()) {
            return '';
        }

        $sections = ["# Agent Context"];

        foreach ($config->presets as $presetName) {
            if ($this->presets->has($presetName)) {
                $sections[] = $this->presets->get($presetName)->content();
            }
        }

        foreach ($config->skills as $skillName) {
            if ($this->skills->has($skillName)) {
                $sections[] = $this->skills->get($skillName)->content();
            }
        }

        if ($config->hasCustomRules()) {
            $sections[] = "## Custom Rules\n\n" . $config->customRules;
        }

        return implode("\n\n---\n\n", $sections);
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
