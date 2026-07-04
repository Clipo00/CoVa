<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;


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

        // Generate and validate ZIP first — before any headers are sent
        $zipPath = $this->generateZip($blueprint, $segments);

        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            throw new \RuntimeException(__('blueprint.zip_generation_failed'));
        }

        $safeFilename = preg_replace('/[^a-z0-9-]/', '', $blueprint->slug).'.zip';
        $fileSize = filesize($zipPath);

        $response = new StreamedResponse(function () use ($zipPath): void {
            $zipHandle = fopen($zipPath, 'rb');
            if ($zipHandle) {
                while (!feof($zipHandle)) {
                    echo fread($zipHandle, 8192);
                    flush();
                }
                fclose($zipHandle);
            }
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
        }, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$safeFilename.'"',
            'Content-Length' => $fileSize,
        ]);

        return $response;
    }

    /**
     * Generate the ZIP file and return its path.
     */
    private function generateZip(Blueprint $blueprint, array $segments): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'bp-zip-');
        $zipOpen = false;

        try {
            $zip = new \ZipArchive;

            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException(__('blueprint.zip_creation_failed'));
            }
            $zipOpen = true;

            // Add agent.md with the router table
            $agentMd = $this->buildAgentMd($segments);
            if ($zip->addFromString('.agents/agent.md', $agentMd) === false) {
                throw new \RuntimeException(__('blueprint.zip_agent_md_failed'));
            }

            // Add individual segment files (skill + custom only, not agent)
            $usedFilenames = [];
            foreach ($segments as $segment) {
                if ($segment['type'] === 'agent') {
                    continue;
                }

                $filename = $segment['filename'];

                if (isset($usedFilenames[$filename])) {
                    \Illuminate\Support\Facades\Log::warning(
                        "Duplicate filename skipped in ZIP: {$filename}",
                        ['blueprint_id' => $blueprint->id]
                    );
                    continue;
                }

                $usedFilenames[$filename] = true;

                if ($zip->addFromString(".agents/.skills/{$filename}", $segment['content']) === false) {
                    throw new \RuntimeException(__('blueprint.zip_add_file_failed', ['filename' => $filename]));
                }
            }

            if ($zip->close() === false) {
                throw new \RuntimeException(__('blueprint.zip_finalize_failed'));
            }
            $zipOpen = false;

            return $zipPath;
        } catch (\Throwable $e) {
            if ($zipOpen) {
                $zip->close();
            }
            if (isset($zipPath) && file_exists($zipPath)) {
                unlink($zipPath);
            }
            throw $e;
        }
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
                continue;
            }

            if ($aiConfig->isEmpty()) {
                continue;
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

        // Add skill + custom segments as table rows (deduplicated to match ZIP contents)
        $usedFilenames = [];
        foreach ($segments as $segment) {
            if ($segment['type'] === 'agent') {
                continue;
            }

            $filename = $segment['filename'];

            if (isset($usedFilenames[$filename])) {
                continue;
            }

            $usedFilenames[$filename] = true;

            $description = $this->extractDescription($segment['content'], $segment['name']);
            $lines[] = "| {$description} | `URL_SKILL/{$filename}` |";
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
