<?php

declare(strict_types=1);

namespace App\Commands;

use App\ApiClient;
use Illuminate\Console\Command;

/**
 * Store the API key in ~/.config/covar/config.json with restricted permissions (0600).
 *
 * Validates connectivity by calling GET /api/me before saving. Does NOT save
 * invalid or expired keys. The base URL is read from config (set at build time)
 * and can be overridden via the --base-url option.
 *
 * Usage:
 *   covar config:set-key covar_abc123
 *   covar config:set-key covar_abc123 --base-url=http://127.0.0.1:8000
 */
class ConfigSetKeyCommand extends Command
{
    /**
     * @var string The console command signature.
     */
    protected $signature = 'config:set-key
        {key : The API key to store (prefix: covar_)}
        {--base-url= : Override the default API base URL}';

    /**
     * @var string The console command description.
     */
    protected $description = 'Set and validate the CoVaR API key';

    private ?ApiClient $apiClient = null;

    // Prevent auto-injection: use setApiClient() for testing instead of constructor DI
    public function setApiClient(?ApiClient $client): void
    {
        $this->apiClient = $client;
    }

    /**
     * Execute the console command.
     *
     * 1. Reads the API key argument and optional --base-url
     * 2. Validates connectivity via GET /api/me
     * 3. On success: saves to config, confirms
     * 4. On failure: shows error, does NOT save
     */
    public function handle(): int
    {
        $key = $this->argument('key');

        // Validate key format: must start with 'covar_' and meet minimum length
        if (!str_starts_with($key, 'covar_')) {
            $this->error('Invalid API key format. Key must start with "covar_".');

            return 1;
        }

        if (strlen($key) < 16) {
            $this->error('Invalid API key. Key must be at least 16 characters long.');

            return 1;
        }

        $client = $this->apiClient ?? $this->createApiClient($key);

        try {
            $client->validateConnectivity();
        } catch (\Throwable $e) {
            $this->error('Invalid API key or token expired');

            return 1;
        }

        $this->saveConfig($key);
        $this->info('API key saved and verified');

        return 0;
    }

    /**
     * Create an ApiClient with the given key.
     */
    private function createApiClient(string $key): ApiClient
    {
        $baseUrl = $this->option('base-url') ?: $this->laravel['config']['app.url'] ?? null;
        if (!$baseUrl) {
            $this->error('No base URL configured. Use --base-url or set app.url in config.');
            exit(1);
        }
        return new ApiClient(null, [
            'base_url' => $baseUrl,
            'api_key' => $key,
        ]);
    }

    /**
     * Save the API key to the config file.
     *
     * Creates the config directory if it doesn't exist. Preserves any
     * existing base_url. Sets restricted permissions (0600) on Unix.
     */
    private function saveConfig(string $key): void
    {
        $path = $this->getConfigPath();
        $dir = dirname($path);

        if (!is_dir($dir)) {
            $mkdirResult = @mkdir($dir, 0755, true);

            if (!$mkdirResult) {
                $this->warn('Unable to create config directory: ' . $dir);
            }
        }

        $config = [];

        if (file_exists($path)) {
            $existing = json_decode(file_get_contents($path), true);

            if (is_array($existing)) {
                $config = $existing;
            }
        }

        $config['api_key'] = $key;

        if (!isset($config['base_url'])) {
            $config['base_url'] = $this->option('base-url') ?? 'https://api.CoVaR.app';
        }

        file_put_contents(
            $path,
            json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        // Set restricted permissions on non-Windows systems
        if (DIRECTORY_SEPARATOR !== '\\') {
            $chmodResult = @chmod($path, 0600);

            if (!$chmodResult) {
                $this->warn('Unable to set restricted permissions (0600) on config file. This may expose your API key.');
            }
        }
    }

    /**
     * Get the config file path.
     *
     * Uses HOME (Unix) or USERPROFILE (Windows) environment variable.
     */
    private function getConfigPath(): string
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE');

        return $home . '/.config/covar/config.json';
    }
}
