<?php

declare(strict_types=1);

namespace App\Commands;

use App\ApiClient;
use Illuminate\Console\Command;

/**
 * Scaffold a project from a CoVa blueprint.
 *
 * Fetches a resolved blueprint from the CoVa API via GET /api/blueprints/{slug},
 * then writes .agent.md, .vscode/extensions.json, .vscode/mcp.json, and .env
 * to the current working directory. If the blueprint contains secret variables,
 * prompts for a password and verifies via POST /api/fetch/{slug}/verify before
 * writing decrypted values.
 *
 * Usage:
 *   covar fetch laravel-api
 *   covar fetch my-blueprint
 */
class FetchCommand extends Command
{
    /**
     * @var string The console command signature.
     */
    protected $signature = 'fetch
        {slug : The blueprint slug to fetch and scaffold}';

    /**
     * @var string The console command description.
     */
    protected $description = 'Scaffold a project from a CoVa blueprint';

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
     * Write .agent.md from the agent_md field.
     */
    private function scaffoldAgentMd(array $result, string $outputDir): void
    {
        $content = $result['agent_md'] ?? null;

        if ($content === null || $content === '') {
            return;
        }

        file_put_contents($outputDir . '/.agent.md', $content);
        $this->line('  <info>✓</info> .agent.md');
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
            @mkdir($vscodeDir, 0755, true);
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
            @mkdir($vscodeDir, 0755, true);
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
     * Prompt for the CoVa password.
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
            "This blueprint contains {$count} secret variables. Enter your CoVa password",
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
        $envPath = $outputDir . '/.env';
        $envContent = file_get_contents($envPath);

        foreach ($decryptedSecrets as $secret) {
            $key = $secret['key'] ?? '';

            if ($key === '') {
                continue;
            }

            $value = $secret['value'] ?? '';
            $line = $key . '=' . $value;
            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';

            if ((bool) preg_match($pattern, $envContent)) {
                $envContent = (string) preg_replace($pattern, $line, $envContent);
            } else {
                $envContent .= $line . "\n";
            }
        }

        file_put_contents($envPath, $envContent);
        $this->info('✓ Secrets decrypted and written to .env');
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
