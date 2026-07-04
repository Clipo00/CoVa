<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\DTOs\McpServersConfig;
use App\Modules\Blueprint\DTOs\ScriptsConfig;
use App\Modules\Blueprint\DTOs\VscodeExtensionsConfig;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Notifications\BlueprintZipPassword;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Generate a ZIP archive containing the blueprint's AI context
 * as a structured .agents/ directory, plus all other tab assets
 * (.env, .mcp/servers.json, .vscode/extensions.json, scripts/install.sh).
 *
 * When the blueprint has secret variables, the ZIP is encrypted
 * with AES-256 and the password is sent via email notification.
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
        $isEncrypted = $this->hasSecrets($blueprint);
        $password = $isEncrypted ? $this->generatePassword() : '';

        // Generate and validate ZIP first — before any headers are sent
        $zipPath = $this->generateZip($blueprint, $segments, $isEncrypted, $password);

        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
            throw new \RuntimeException(__('blueprint.zip_generation_failed'));
        }

        // Send password notification AFTER response is sent to client,
        // so SMTP communication cannot corrupt the ZIP stream
        if ($isEncrypted && $password !== '') {
            $this->sendPasswordNotificationDeferred($blueprint, $password);
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
     * Check if the blueprint has any secret variables.
     */
    public function hasSecrets(Blueprint $blueprint): bool
    {
        $variables = $blueprint->variables;

        if ($variables === null || $variables->isEmpty()) {
            return false;
        }

        foreach ($variables as $variable) {
            if ($variable->is_secret) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a cryptographically secure random password (32-char hex).
     */
    public function generatePassword(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Build .env file content from blueprint variables.
     * When $includeSecrets is true, secret values are included (decrypted).
     * When false, secret values are emitted as empty strings.
     */
    public function buildEnvContent(Blueprint $blueprint, bool $includeSecrets = false): string
    {
        $variables = $blueprint->variables;

        if ($variables === null || $variables->isEmpty()) {
            return '';
        }

        $lines = [];

        // Group variables by section
        $grouped = $variables->groupBy(fn ($v) => $v->section ?? 'General');

        foreach ($grouped as $section => $vars) {
            $lines[] = '# --- '.$section.' ---';

            foreach ($vars as $variable) {
                if (empty($variable->key)) {
                    continue;
                }

                if ($variable->is_secret) {
                    $value = $includeSecrets ? ($variable->default_value ?? '') : '';
                } else {
                    $value = $variable->default_value ?? '';
                }

                $lines[] = $variable->key.'='.$value;
            }
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * Generate the ZIP file and return its path.
     */
    private function generateZip(Blueprint $blueprint, array $segments, bool $isEncrypted = false, string $password = ''): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'bp-zip-');
        $zipOpen = false;

        try {
            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
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
                    error_log("Duplicate filename skipped in ZIP: {$filename}");
                    continue;
                }

                $usedFilenames[$filename] = true;

                if ($zip->addFromString(".agents/.skills/{$filename}", $segment['content']) === false) {
                    throw new \RuntimeException(__('blueprint.zip_add_file_failed', ['filename' => $filename]));
                }
            }

            // Add full blueprint assets (from all tab types)
            $this->addEnvFile($zip, $blueprint, $isEncrypted);
            $this->addMcpServersJson($zip, $blueprint);
            $this->addVscodeExtensionsJson($zip, $blueprint);
            $this->addScriptsSh($zip, $blueprint);

            // Encrypt all entries if needed
            if ($isEncrypted && $password !== '') {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if ($name !== false) {
                        $zip->setEncryptionName($name, ZipArchive::EM_TRAD_PKWARE, $password);
                    }
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
     * Add .env file to the ZIP from blueprint variables.
     */
    private function addEnvFile(ZipArchive $zip, Blueprint $blueprint, bool $includeSecrets): void
    {
        $content = $this->buildEnvContent($blueprint, $includeSecrets);

        if ($content === '') {
            return;
        }

        if ($zip->addFromString('.env', $content) === false) {
            throw new \RuntimeException(__('blueprint.zip_add_file_failed', ['filename' => '.env']));
        }
    }

    /**
     * Add .mcp/servers.json to the ZIP from the MCP servers tab config.
     */
    private function addMcpServersJson(ZipArchive $zip, Blueprint $blueprint): void
    {
        $tabsConfig = $blueprint->tabs_config ?? [];

        if (!is_array($tabsConfig)) {
            return;
        }

        foreach ($tabsConfig as $tabData) {
            if (!is_array($tabData)) {
                continue;
            }

            if (($tabData['type'] ?? '') !== 'mcp_servers') {
                continue;
            }

            try {
                $config = McpServersConfig::fromArray($tabData['config'] ?? []);
            } catch (\InvalidArgumentException) {
                continue;
            }

            if (!$config->hasServers()) {
                return;
            }

            $servers = array_map(fn ($entry) => $entry->toArray(), $config->servers);
            $json = json_encode($servers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($json === false) {
                throw new \RuntimeException(__('blueprint.zip_add_file_failed', ['filename' => '.mcp/servers.json']));
            }

            if ($zip->addFromString('.mcp/servers.json', $json) === false) {
                throw new \RuntimeException(__('blueprint.zip_add_file_failed', ['filename' => '.mcp/servers.json']));
            }

            return;
        }
    }

    /**
     * Add .vscode/extensions.json to the ZIP from the VSCode extensions tab config.
     */
    private function addVscodeExtensionsJson(ZipArchive $zip, Blueprint $blueprint): void
    {
        $tabsConfig = $blueprint->tabs_config ?? [];

        if (!is_array($tabsConfig)) {
            return;
        }

        foreach ($tabsConfig as $tabData) {
            if (!is_array($tabData)) {
                continue;
            }

            if (($tabData['type'] ?? '') !== 'vscode_extensions') {
                continue;
            }

            try {
                $config = VscodeExtensionsConfig::fromArray($tabData['config'] ?? []);
            } catch (\InvalidArgumentException) {
                continue;
            }

            if (!$config->hasExtensions()) {
                return;
            }

            $json = json_encode($config->extensions, JSON_PRETTY_PRINT);

            if ($json === false) {
                throw new \RuntimeException(__('blueprint.zip_add_file_failed', ['filename' => '.vscode/extensions.json']));
            }

            if ($zip->addFromString('.vscode/extensions.json', $json) === false) {
                throw new \RuntimeException(__('blueprint.zip_add_file_failed', ['filename' => '.vscode/extensions.json']));
            }

            return;
        }
    }

    /**
     * Add scripts/install.sh to the ZIP from the scripts tab config.
     */
    private function addScriptsSh(ZipArchive $zip, Blueprint $blueprint): void
    {
        $tabsConfig = $blueprint->tabs_config ?? [];

        if (!is_array($tabsConfig)) {
            return;
        }

        foreach ($tabsConfig as $tabData) {
            if (!is_array($tabData)) {
                continue;
            }

            if (($tabData['type'] ?? '') !== 'scripts') {
                continue;
            }

            try {
                $config = ScriptsConfig::fromArray($tabData['config'] ?? []);
            } catch (\InvalidArgumentException) {
                continue;
            }

            if (!$config->hasScripts()) {
                return;
            }

            $scriptContent = $config->toShellScript();

            if ($scriptContent === '') {
                return;
            }

            if ($zip->addFromString('scripts/install.sh', $scriptContent) === false) {
                throw new \RuntimeException(__('blueprint.zip_add_file_failed', ['filename' => 'scripts/install.sh']));
            }

            return;
        }
    }

    /**
     * Send password notification deferred to after response (web context)
     * to prevent SMTP output from corrupting the ZIP stream.
     * Falls back to inline sending when dispatcher is unavailable (tests, CLI).
     */
    private function sendPasswordNotificationDeferred(Blueprint $blueprint, string $password): void
    {
        $title = $blueprint->title;

        try {
            dispatch(function () use ($title, $password): void {
                $user = auth()->user();
                if ($user !== null) {
                    $user->notify(new BlueprintZipPassword(
                        blueprintTitle: $title,
                        password: $password,
                    ));
                }
            })->afterResponse();
        } catch (\Throwable) {
            // Dispatcher not available — fall back to inline
            $this->sendPasswordNotification($blueprint, $password);
        }
    }

    /**
     * Send password notification inline.
     * Gracefully handles missing auth context (e.g., unit tests).
     */
    private function sendPasswordNotification(Blueprint $blueprint, string $password): void
    {
        try {
            $user = auth()->user();
            if ($user !== null) {
                $user->notify(new BlueprintZipPassword(
                    blueprintTitle: $blueprint->title,
                    password: $password,
                ));
            }
        } catch (\Throwable $e) {
            error_log('Failed to send ZIP password notification: '.$e->getMessage());
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
