<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tabs\AiContext;

use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\DTOs\AiContextSegment;
use App\Modules\Blueprint\Tabs\AiContext\Agents\AgentRegistry;

class AgentGenerator
{
    public function __construct(
        private readonly SegmentRegistry $presets,
        private readonly SegmentRegistry $skills,
        private readonly AgentRegistry $agents,
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

        $segments = $this->resolveSegments($config);

        $sections = ['# Agent Context'];

        foreach ($segments as $seg) {
            $sections[] = $seg['content'];
        }

        return implode("\n\n---\n\n", $sections);
    }

    /**
     * Resolve segments into structured entries with name, filename, and content.
     *
     * Each entry contains:
     * - name: original segment name
     * - filename: sanitized filename with .md extension
     * - content: resolved markdown content (with heading)
     *
     * @return array<int, array{name: string, filename: string, content: string}>
     */
    public function resolveSegments(AiContextConfig $config): array
    {
        if ($config->isEmpty()) {
            return [];
        }

        $result = [];

        foreach ($config->segments as $segment) {
            $content = $this->resolveContent($segment);

            if ($content === null) {
                continue;
            }

            $result[] = [
                'name' => $segment->name,
                'filename' => $this->sanitizeFilename($segment->name),
                'content' => $content,
            ];
        }

        return $result;
    }

    /**
     * Sanitize a segment name to a safe filename.
     *
     * Lowercase, alphanumeric and hyphens only, with .md extension.
     */
    private function sanitizeFilename(string $name): string
    {
        $filename = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $filename = trim($filename, '-');

        return ($filename === '' ? 'untitled' : $filename).'.md';
    }

    /**
     * Resolve markdown content for a single segment.
     *
     * Precedence: segment override > registry default > null (skip).
     */
    private function resolveContent(AiContextSegment $segment): ?string
    {
        // Override content (custom, agent with user edits): use provided content with heading
        if ($segment->content !== null) {
            $heading = "## {$segment->name}";

            if ($segment->content === '') {
                return $heading;
            }

            return "{$heading}\n\n{$segment->content}";
        }

        // Registry content (preset/skill/agent with null content)
        $registry = null;
        if ($segment->isPreset()) {
            $registry = $this->presets;
        } elseif ($segment->isSkill()) {
            $registry = $this->skills;
        } elseif ($segment->isAgent()) {
            $registry = $this->agents;
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

    /**
     * Get all available agent names.
     *
     * @return string[]
     */
    public function agentNames(): array
    {
        return $this->agents->names();
    }
}
