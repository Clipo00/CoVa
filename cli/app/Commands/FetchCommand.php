<?php

declare(strict_types=1);

namespace App\Commands;

use App\ApiClient;
use Illuminate\Console\Command;

/**
 * Scaffold a project from a CoVaR blueprint.
 *
 * Fetches a resolved blueprint from the CoVaR API via GET /api/blueprints/{slug},
 * then writes .agent.md, .vscode/extensions.json, .vscode/mcp.json, and .env
 * to the current working directory. If the blueprint contains secret variables,
 * prompts for a password and verifies via POST /api/fetch/{slug}/verify before
 * writing decrypted values.
 *
 * Usage:
 *   covar vault:fetch laravel-api
 *   covar vault:fetch my-blueprint
 */
class FetchCommand extends Command
{
    /**
     * @var string The console command signature.
     */
    protected $signature = 'vault:fetch
        {slug : The blueprint slug to fetch and scaffold}';

    /**
     * @var string The console command description.
     */
    protected $description = 'Scaffold a project from a CoVaR blueprint';

    private ?ApiClient $apiClient;

    /**
     * @param ApiClient|null $apiClient Optional injected client for testing
     */
    public function __construct(?ApiClient $apiClient = null)
    {
        parent::__construct();
        $this->apiClient = $apiClient;
    }

    /**
     * Execute the console command.
     *
     * 1. Resolves the blueprint via GET /api/blueprints/{slug}
     * 2. Scaffolds .agent.md, .vscode/extensions.json, .vscode/mcp.json, .env
     * 3. If secrets exist: prompts password, verifies, writes decrypted values
     * 4. Shows success summary
     *
     * @return int Exit code (0 for success, 1 for error)
     */
    public function handle(): int
    {
        $slug = $this->argument('slug');
        $client = $this->apiClient ?? new ApiClient();

        try {
            $result = $client->get("/api/blueprints/{$slug}");
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'Not found') {
                $this->error("Blueprint not found: {$slug}");
            } else {
                $this->error($e->getMessage());
            }

            return 1;
        }

        $outputDir = getcwd();

        $this->scaffoldAgentMd($result, $outputDir);
        $this->scaffoldVscodeExtensions($result, $outputDir);
        $this->scaffoldMcpServers($result, $outputDir);
        $this->scaffoldEnv($result, $outputDir);

        $secrets = $this->getSecretVariables($result);

        if (!empty($secrets)) {
            $this->handleSecrets($slug, $client, $secrets, $outputDir);
        }

        $this->showSummary($result);

        return 0;
    }

    /**
     * Scaffold agent context files.
     *
     * If the response contains ai_context_segments (new format), creates the
     * .agents/ directory structure with agent.md and .skills/*.md files.
     * Otherwise falls back to the legacy .agent.md single file.
     */
    private function scaffoldAgentMd(array $result, string $outputDir): void
    {
        $segments = $result['ai_context_segments'] ?? [];

        if (!empty($segments)) {
            $this->scaffoldAgentsDirectory($segments, $outputDir);

            return;
        }

        // Legacy: write .agent.md from agent_md field
        $content = $result['agent_md'] ?? null;

        if ($content === null || $content === '') {
            return;
        }

        file_put_contents($outputDir . '/.agent.md', $content);
        $this->line('  <info>✓</info> .agent.md');
    }

    /**
     * Create .agents/ directory structure with router table and skill files.
     *
     * @param array<int, array{type: string, name: string, filename: string, content: string}> $segments
     */
    private function scaffoldAgentsDirectory(array $segments, string $outputDir): void
    {
        $agentsDir = $outputDir . '/.agents';

        if (!is_dir($agentsDir)) {
            $mkdirResult = mkdir($agentsDir, 0755, true);

            if (!$mkdirResult) {
                $this->error("Failed to create directory: {$agentsDir}");

                return;
            }
        }

        $skillsDir = $agentsDir . '/.skills';

        if (!is_dir($skillsDir)) {
            $mkdirResult = mkdir($skillsDir, 0755, true);

            if (!$mkdirResult) {
                $this->error("Failed to create directory: {$skillsDir}");

                return;
            }
        }

        // Build agent.md with router table
        $agentMd = $this->buildAgentMdFromSegments($segments);
        file_put_contents($agentsDir . '/agent.md', $agentMd);
        $this->line('  <info>✓</info> .agents/agent.md');

        // Write individual skill files (skip agent-type segments — they are included in the preamble)
        $seenNames = [];

        foreach ($segments as $segment) {
            $name = $segment['name'] ?? '';

            if ($name === '') {
                continue;
            }

            if (($segment['type'] ?? '') === 'agent') {
                continue;
            }

            if (isset($seenNames[$name])) {
                $this->warn("Duplicate segment name '{$name}' — keeping first occurrence");

                continue;
            }

            $seenNames[$name] = true;

            file_put_contents(
                $skillsDir . '/' . ($segment['filename'] ?? $this->sanitizeFilename($name)),
                $segment['content'] ?? '',
            );
            $this->line("  <info>✓</info> .agents/.skills/{$segment['filename']}");
        }
    }

    /**
     * Build agent.md content with a Project Skills router table
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
     *
     * @param array<int, array{type: string, name: string, filename: string, content: string}> $segments
     */
    private function buildAgentMdFromSegments(array $segments): string
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
            if (($segment['type'] ?? '') === 'agent') {
                continue;
            }

            $description = $this->extractDescription(
                $segment['content'] ?? '',
                $segment['name'] ?? '',
            );
            $lines[] = "| {$description} | `URL_SKILL/{$segment['filename']}` |";
        }

        // Add agent preamble content after the table
        $hasAgentContent = false;

        foreach ($segments as $segment) {
            if (($segment['type'] ?? '') !== 'agent') {
                continue;
            }

            if (!$hasAgentContent) {
                $lines[] = '';
                $hasAgentContent = true;
            }

            $lines[] = $segment['content'] ?? '';
        }

        return implode("\n", $lines) . "\n";
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

    /**
     * Sanitize a segment name to a safe filename.
     *
     * Lowercase, alphanumeric and hyphens only, with .md extension.
     */
    private function sanitizeFilename(string $name): string
    {
        $filename = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $filename = trim($filename, '-');

        return ($filename === '' ? 'untitled' : $filename) . '.md';
    }

    /**
     * Write .vscode/extensions.json from vscode_extensions.
     *
     * Creates the .vscode/ directory if it doesn't exist.
     */
    private function scaffoldVscodeExtensions(array $result, string $outputDir): void
    {
        $extensions = $result['vscode_extensions'] ?? [];

        if (empty($extensions)) {
            return;
        }

        $vscodeDir = $outputDir . '/.vscode';

        if (!is_dir($vscodeDir)) {
            $mkdirResult = mkdir($vscodeDir, 0755, true);

            if (!$mkdirResult) {
                $this->error("Failed to create directory: {$vscodeDir}");

                return;
            }
        }

        file_put_contents(
            $vscodeDir . '/extensions.json',
            json_encode(
                ['recommendations' => array_values($extensions)],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            ) . "\n",
        );
        $this->line('  <info>✓</info> .vscode/extensions.json');
    }

    /**
     * Write .vscode/mcp.json from mcp_servers.
     *
     * Transforms the server list into a map keyed by server name.
     * Creates the .vscode/ directory if it doesn't exist.
     */
    private function scaffoldMcpServers(array $result, string $outputDir): void
    {
        $mcpData = $result['mcp_servers'] ?? [];
        $servers = $mcpData['mcp_servers'] ?? [];

        if (empty($servers)) {
            return;
        }

        $vscodeDir = $outputDir . '/.vscode';

        if (!is_dir($vscodeDir)) {
            $mkdirResult = mkdir($vscodeDir, 0755, true);

            if (!$mkdirResult) {
                $this->error("Failed to create directory: {$vscodeDir}");

                return;
            }
        }

        $mcpServers = [];

        foreach ($servers as $server) {
            $name = $server['name'] ?? '';

            if ($name === '') {
                continue;
            }

            $mcpServers[$name] = [
                'command' => $server['command'] ?? '',
                'args' => $server['args'] ?? [],
            ];
        }

        file_put_contents(
            $vscodeDir . '/mcp.json',
            json_encode(
                ['mcpServers' => $mcpServers],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            ) . "\n",
        );
        $this->line('  <info>✓</info> .vscode/mcp.json');
    }

    /**
     * Write .env from blueprint variables.
     *
     * Secret variables are written with empty values initially.
     * They are updated later after password verification.
     */
    private function scaffoldEnv(array $result, string $outputDir): void
    {
        $variables = $result['variables'] ?? [];

        $lines = [];

        foreach ($variables as $variable) {
            $key = $variable['key'] ?? '';

            if ($key === '') {
                continue;
            }

            $isSecret = (bool) ($variable['is_secret'] ?? false);
            $value = $isSecret ? '' : ($variable['default_value'] ?? '');
            $value = $this->escapeEnvValue($value);

            $lines[] = $key . '=' . $value;
        }

        file_put_contents(
            $outputDir . '/.env',
            implode("\n", $lines) . "\n",
        );
        $this->line('  <info>✓</info> .env');
    }

    /**
     * Filter variables to only those with is_secret === true.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSecretVariables(array $result): array
    {
        $variables = $result['variables'] ?? [];

        return array_values(
            array_filter($variables, fn (array $v): bool => (bool) ($v['is_secret'] ?? false)),
        );
    }

    /**
     * Prompt for the CoVaR password.
     *
     * Extracted as a protected method so tests can mock it without
     * dealing with Windows hiddeninput.exe limitation.
     */
    protected function promptPassword(string $message): string
    {
        return $this->secret($message);
    }

    private function handleSecrets(string $slug, ApiClient $client, array $secrets, string $outputDir): void
    {
        $count = count($secrets);
        $password = $this->promptPassword(
            "This blueprint contains {$count} secret variables. Enter your CoVaR password",
        );

        try {
            $response = $client->post("/api/fetch/{$slug}/verify", [
                'password' => $password,
            ]);
        } catch (\RuntimeException $e) {
            $this->warn('Password verification failed. Secret variables written with empty values — fill them manually in .env');

            return;
        }

        $decryptedSecrets = $response['secrets'] ?? [];

        if (!is_array($decryptedSecrets)) {
            $this->warn('Unexpected response from password verification. Secret variables written with empty values — fill them manually in .env');

            return;
        }

        $envPath = $outputDir . '/.env';
        $envContent = file_get_contents($envPath);

        foreach ($decryptedSecrets as $secret) {
            $key = $secret['key'] ?? '';

            if ($key === '') {
                continue;
            }

            $value = $secret['value'] ?? '';
            $escapedValue = $this->escapeEnvValue($value);
            $line = $key . '=' . $escapedValue;
            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

            if ((bool) preg_match($pattern, $envContent)) {
                $escapedLine = preg_replace('/\$/', '\\\\$', $line);
                $envContent = (string) preg_replace($pattern, $escapedLine, $envContent);
            } else {
                $envContent .= $line . "\n";
            }
        }

        file_put_contents($envPath, $envContent);
        $this->info('✓ Secrets decrypted and written to .env');
    }

    /**
     * Escape a value for safe inclusion in a .env file.
     *
     * Wraps the value in double quotes and escapes internal quotes
     * if it contains spaces, hash (#), or quote characters.
     */
    private function escapeEnvValue(string $value): string
    {
        if ($value === '' || preg_match('/[\s"#]/', $value) === 0) {
            return $value;
        }

        $escaped = str_replace('"', '\\"', $value);

        return '"' . $escaped . '"';
    }

    /**
     * Show a success summary after scaffolding.
     */
    private function showSummary(array $result): void
    {
        $this->line('');
        $this->info("Blueprint \"{$result['title']}\" scaffolded successfully");
    }
}
