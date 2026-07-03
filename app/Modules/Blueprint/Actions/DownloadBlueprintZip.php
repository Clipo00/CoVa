<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Generate a ZIP archive containing the blueprint's AI context
 * as a structured .agents/ directory.
 *
 * The ZIP contains:
 * - .agents/agent.md — router table with all skill/custom segments + agent preamble
 * - .agents/.skills/{name}.md — individual segment content files
 */
class DownloadBlueprintZip
{
    public function __construct(
        private readonly AgentGenerator $agentGenerator,
    ) {}

    /**
     * Generate and return a ZIP stream response for the given blueprint.
     */
    public function execute(Blueprint $blueprint): StreamedResponse
    {
        $segments = $this->resolveBlueprintSegments($blueprint);

        return new StreamedResponse(function () use ($blueprint, $segments): void {
            $zipPath = tempnam(sys_get_temp_dir(), 'bp-zip-');

            try {
                $zip = new ZipArchive;

                if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    throw new \RuntimeException('Cannot create ZIP archive.');
                }

                // Add agent.md with the router table
                $agentMd = $this->buildAgentMd($segments);
                $zip->addFromString('.agents/agent.md', $agentMd);

                // Add individual segment files (skill + custom only, not agent)
                foreach ($segments as $segment) {
                    if ($segment['type'] === 'agent') {
                        continue;
                    }

                    $zip->addFromString(
                        ".agents/.skills/{$segment['filename']}",
                        $segment['content'],
                    );
                }

                $zip->close();

                // Output ZIP content
                readfile($zipPath);
            } finally {
                if (isset($zipPath) && file_exists($zipPath)) {
                    unlink($zipPath);
                }
            }
        }, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$blueprint->slug.'.zip"',
        ]);
    }

    /**
     * Resolve segments from the blueprint's tabs_config, preserving type information.
     *
     * @return array<int, array{type: string, name: string, filename: string, content: string}>
     */
    private function resolveBlueprintSegments(Blueprint $blueprint): array
    {
        $tabsConfig = $blueprint->tabs_config ?? [];

        if (!is_array($tabsConfig)) {
            return [];
        }

        foreach ($tabsConfig as $tabData) {
            if (!is_array($tabData)) {
                continue;
            }

            if (($tabData['type'] ?? '') !== 'ai_context') {
                continue;
            }

            try {
                $aiConfig = AiContextConfig::fromArray($tabData['config'] ?? []);
            } catch (\InvalidArgumentException) {
                return [];
            }

            if ($aiConfig->isEmpty()) {
                return [];
            }

            return $this->resolveSegmentsWithTypes($aiConfig);
        }

        return [];
    }

    /**
     * Resolve each segment individually to preserve type info.
     *
     * @return array<int, array{type: string, name: string, filename: string, content: string}>
     */
    private function resolveSegmentsWithTypes(AiContextConfig $config): array
    {
        $result = [];

        foreach ($config->segments as $segment) {
            // Resolve this segment individually
            $singleConfig = new AiContextConfig(segments: [$segment]);
            $resolved = $this->agentGenerator->resolveSegments($singleConfig);

            if (empty($resolved)) {
                continue;
            }

            $result[] = [
                'type' => $segment->type,
                'name' => $segment->name,
                'filename' => $resolved[0]['filename'],
                'content' => $resolved[0]['content'],
            ];
        }

        return $result;
    }

    /**
     * Build the agent.md content with a Project Skills router table
     * and optional agent preamble content.
     *
     * Format:
     *   # Agent Context
     *
     *   ## Project Skills
     *
     *   | Name | File |
     *   |------|------|
     *   | {description} | `URL_SKILL/{filename}` |
     *   ...
     *
     *   {agent preamble content}
     */
    private function buildAgentMd(array $segments): string
    {
        $lines = [
            '# Agent Context',
            '',
            '## Project Skills',
            '',
            '| Name | File |',
            '|------|------|',
        ];

        // Add skill + custom segments as table rows
        foreach ($segments as $segment) {
            if ($segment['type'] === 'agent') {
                continue;
            }

            $description = $this->extractDescription($segment['content'], $segment['name']);
            $lines[] = "| {$description} | `URL_SKILL/{$segment['filename']}` |";
        }

        // Add agent preamble content after the table
        $hasAgentContent = false;

        foreach ($segments as $segment) {
            if ($segment['type'] !== 'agent') {
                continue;
            }

            if (!$hasAgentContent) {
                $lines[] = '';
                $hasAgentContent = true;
            }

            $lines[] = $segment['content'];
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * Extract the first ## heading from markdown content as the description.
     * Falls back to the segment name if no heading is found.
     */
    private function extractDescription(string $content, string $fallback): string
    {
        if (preg_match('/^## (.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return $fallback;
    }
}
