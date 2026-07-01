<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

/**
 * Guzzle HTTP client wrapper for the CoVa API.
 *
 * Reads configuration from ~/.config/covar/config.json and handles
 * authentication via Bearer token. Maps HTTP errors to user-friendly messages.
 */
class ApiClient
{
    private Client $http;
    private array $config;

    /**
     * @param Client|null $http  Optional Guzzle client (for testing/mocking)
     * @param array|null  $config Optional config array (for testing/overrides)
     */
    public function __construct(?Client $http = null, ?array $config = null)
    {
        $this->config = $config ?? $this->loadConfig();
        $this->http = $http ?? $this->createHttpClient();
    }

    /**
     * Send a GET request to the given endpoint.
     *
     * @return array Decoded JSON response
     *
     * @throws \RuntimeException On HTTP or network errors
     */
    public function get(string $endpoint): array
    {
        try {
            $response = $this->http->get($endpoint, [
                'headers' => $this->authHeaders(),
            ]);

            return json_decode((string) $response->getBody(), true) ?? [];
        } catch (ConnectException $e) {
            throw new \RuntimeException('Network error: unable to reach the CoVa API');
        } catch (RequestException $e) {
            $this->handleError($e);
        }
    }

    /**
     * Send a POST request to the given endpoint with JSON data.
     *
     * @return array Decoded JSON response
     *
     * @throws \RuntimeException On HTTP or network errors
     */
    public function post(string $endpoint, array $data = []): array
    {
        try {
            $response = $this->http->post($endpoint, [
                'headers' => $this->authHeaders(),
                'json' => $data,
            ]);

            return json_decode((string) $response->getBody(), true) ?? [];
        } catch (ConnectException $e) {
            throw new \RuntimeException('Network error: unable to reach the CoVa API');
        } catch (RequestException $e) {
            $this->handleError($e);
        }
    }

    /**
     * Validate connectivity by calling GET /api/me.
     *
     * Returns true if the API responds successfully, false on any error.
     */
    public function validateConnectivity(): bool
    {
        try {
            $this->get('/api/me');

            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Build the Authorization header array.
     *
     * @return array<string, string>
     */
    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->config['api_key'],
            'Accept' => 'application/json',
        ];
    }

    /**
     * Create the default Guzzle HTTP client.
     */
    private function createHttpClient(): Client
    {
        return new Client([
            'base_uri' => $this->config['base_url'],
            'timeout' => 10,
            'http_errors' => true,
        ]);
    }

    /**
     * Map HTTP status codes to user-friendly error messages.
     *
     * @throws \RuntimeException Always
     */
    private function handleError(RequestException $e): never
    {
        $response = $e->getResponse();
        $status = $response ? $response->getStatusCode() : 0;

        $message = match (true) {
            $status === 401 => 'Authentication failed. Run covar config set-key <key>',
            $status === 403 => 'API access requires Pro or Enterprise plan',
            $status === 404 => 'Not found',
            $status === 429 => 'Rate limit exceeded',
            default => 'Server error',
        };

        throw new \RuntimeException($message);
    }

    /**
     * Load configuration from ~/.config/covar/config.json.
     *
     * @return array{base_url: string, api_key: string}
     *
     * @throws \RuntimeException If config file is missing or invalid
     */
    private function loadConfig(): array
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE');
        $path = $home . '/.config/covar/config.json';

        if (!file_exists($path)) {
            throw new \RuntimeException('Config file not found. Run covar config set-key <key>');
        }

        $config = json_decode(file_get_contents($path), true);

        if (!is_array($config) || !isset($config['api_key'])) {
            throw new \RuntimeException('API key not configured. Run covar config set-key <key>');
        }

        if (!isset($config['base_url'])) {
            $config['base_url'] = 'https://api.cova.app';
        }

        return $config;
    }
}
