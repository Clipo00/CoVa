<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\ApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    private const TEST_BASE_URL = 'https://api.CoVaR.app';
    private const TEST_API_KEY = 'cova_test123';

    /**
     * Create an ApiClient with a mock Guzzle handler for testing.
     */
    private function createClientWithMock(MockHandler $mock, ?array $config = null): ApiClient
    {
        $handlerStack = HandlerStack::create($mock);
        $http = new Client(['handler' => $handlerStack]);

        return new ApiClient($http, $config ?? [
            'base_url' => self::TEST_BASE_URL,
            'api_key' => self::TEST_API_KEY,
        ]);
    }

    #[Test]
    public function sends_bearer_token_in_authorization_header(): void
    {
        $mock = new MockHandler([
            function (Request $request) {
                $this->assertEquals(
                    'Bearer ' . self::TEST_API_KEY,
                    $request->getHeaderLine('Authorization')
                );

                return new Response(200, [], json_encode(['user' => ['name' => 'Test']]));
            },
        ]);

        $client = $this->createClientWithMock($mock);
        $result = $client->get('/api/me');

        $this->assertSame('Test', $result['user']['name']);
    }

    #[Test]
    public function sends_accept_json_header(): void
    {
        $mock = new MockHandler([
            function (Request $request) {
                $this->assertEquals(
                    'application/json',
                    $request->getHeaderLine('Accept')
                );

                return new Response(200, [], '{}');
            },
        ]);

        $client = $this->createClientWithMock($mock);
        $client->get('/api/me');

        $this->assertTrue(true);
    }

    #[Test]
    public function get_returns_decoded_json(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['slug' => 'laravel-api', 'title' => 'Laravel API'],
                    ['slug' => 'react-app', 'title' => 'React App'],
                ],
            ])),
        ]);

        $client = $this->createClientWithMock($mock);
        $result = $client->get('/api/blueprints');

        $this->assertCount(2, $result['data']);
        $this->assertSame('laravel-api', $result['data'][0]['slug']);
    }

    #[Test]
    public function post_sends_json_data(): void
    {
        $mock = new MockHandler([
            function (Request $request) {
                $this->assertEquals('POST', $request->getMethod());
                $body = json_decode((string) $request->getBody(), true);

                $this->assertSame('secret123', $body['password']);

                return new Response(200, [], json_encode([
                    'secrets' => [
                        ['key' => 'DB_PASSWORD', 'value' => 'decrypted_value'],
                    ],
                ]));
            },
        ]);

        $client = $this->createClientWithMock($mock);
        $result = $client->post('/api/fetch/laravel-api/verify', [
            'password' => 'secret123',
        ]);

        $this->assertCount(1, $result['secrets']);
        $this->assertSame('DB_PASSWORD', $result['secrets'][0]['key']);
    }

    #[Test]
    public function maps_401_to_authentication_error(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Authentication failed');

        $mock = new MockHandler([
            new Response(401),
        ]);

        $client = $this->createClientWithMock($mock);
        $client->get('/api/me');
    }

    #[Test]
    public function maps_403_to_plan_required_error(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API access requires Pro or Enterprise plan');

        $mock = new MockHandler([
            new Response(403),
        ]);

        $client = $this->createClientWithMock($mock);
        $client->get('/api/blueprints');
    }

    #[Test]
    public function maps_404_to_not_found_error(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not found');

        $mock = new MockHandler([
            new Response(404),
        ]);

        $client = $this->createClientWithMock($mock);
        $client->get('/api/blueprints/nonexistent');
    }

    #[Test]
    public function maps_429_to_rate_limit_error(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Rate limit exceeded');

        $mock = new MockHandler([
            new Response(429),
        ]);

        $client = $this->createClientWithMock($mock);
        $client->get('/api/me');
    }

    #[Test]
    public function maps_500_to_server_error(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Server error');

        $mock = new MockHandler([
            new Response(500),
        ]);

        $client = $this->createClientWithMock($mock);
        $client->get('/api/me');
    }

    #[Test]
    public function maps_network_errors_to_user_friendly_message(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Network error: unable to reach the CoVaR API');

        $mock = new MockHandler([
            new ConnectException(
                'cURL error 7: Failed to connect',
                new Request('GET', '/api/me')
            ),
        ]);

        $client = $this->createClientWithMock($mock);
        $client->get('/api/me');
    }

    #[Test]
    public function validate_connectivity_returns_true_on_success(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['user' => ['name' => 'Test']])),
        ]);

        $client = $this->createClientWithMock($mock);

        $this->assertTrue($client->validateConnectivity());
    }

    #[Test]
    public function validate_connectivity_returns_false_on_auth_failure(): void
    {
        $mock = new MockHandler([
            new Response(401),
        ]);

        $client = $this->createClientWithMock($mock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Authentication failed');

        $client->validateConnectivity();
    }

    #[Test]
    public function validate_connectivity_returns_false_on_network_error(): void
    {
        $mock = new MockHandler([
            new ConnectException(
                'cURL error 7: Failed to connect',
                new Request('GET', '/api/me')
            ),
        ]);

        $client = $this->createClientWithMock($mock);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Network error');

        $client->validateConnectivity();
    }

    #[Test]
    public function uses_config_base_url_for_requests(): void
    {
        $mock = new MockHandler([
            function (Request $request) {
                $this->assertStringStartsWith('https://custom.CoVaR.app', (string) $request->getUri());

                return new Response(200, [], '{}');
            },
        ]);

        $handlerStack = HandlerStack::create($mock);
        $http = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'https://custom.CoVaR.app',
        ]);

        $client = new ApiClient($http, [
            'base_url' => 'https://custom.CoVaR.app',
            'api_key' => self::TEST_API_KEY,
        ]);

        $client->get('/api/me');

        $this->assertTrue(true);
    }
}
