<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\ApiClient;
use App\Commands\ListCommand;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    /**
     * Create a mock ApiClient with the given return data or exception.
     */
    private function mockApiClient(?array $returnData = null, ?\RuntimeException $exception = null): ApiClient
    {
        $mock = $this->createMock(ApiClient::class);

        if ($exception !== null) {
            $mock->method('get')->willThrowException($exception);
        } else {
            $mock->method('get')->willReturn($returnData ?? []);
        }

        $this->container->instance(ApiClient::class, $mock);

        return $mock;
    }

    /**
     * Create a CommandTester for ListCommand with an optional pre-configured ApiClient.
     */
    private function createCommandTester(?ApiClient $client = null): CommandTester
    {
        $command = new ListCommand($client);
        $command->setLaravel($this->container);

        return new CommandTester($command);
    }

    // ----- Happy path -----

    #[Test]
    public function displays_blueprints_as_table_with_slug_and_title(): void
    {
        $data = [
            'data' => [
                ['slug' => 'laravel-api', 'title' => 'Laravel API Starter'],
                ['slug' => 'react-app', 'title' => 'React Application'],
            ],
        ];

        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);

        $display = $tester->getDisplay();

        // Must contain both blueprints
        $this->assertStringContainsString('laravel-api', $display);
        $this->assertStringContainsString('Laravel API Starter', $display);
        $this->assertStringContainsString('react-app', $display);
        $this->assertStringContainsString('React Application', $display);

        // Must have table headers
        $this->assertStringContainsString('Slug', $display);
        $this->assertStringContainsString('Title', $display);
    }

    #[Test]
    public function with_g_option_includes_description_column(): void
    {
        $data = [
            'data' => [
                [
                    'slug' => 'laravel-api',
                    'title' => 'Laravel API Starter',
                    'description' => 'A Laravel API starter kit with auth',
                ],
            ],
        ];

        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['--with-descriptions' => true]);

        $this->assertSame(0, $exitCode);

        $display = $tester->getDisplay();

        // Description column must be present
        $this->assertStringContainsString('Description', $display);
        $this->assertStringContainsString('A Laravel API starter kit with auth', $display);

        // Slug and Title must still be present
        $this->assertStringContainsString('Slug', $display);
        $this->assertStringContainsString('Title', $display);
    }

    #[Test]
    public function displays_empty_state_when_no_blueprints(): void
    {
        $mock = $this->mockApiClient(['data' => []]);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No blueprints found', $tester->getDisplay());
    }

    // ----- Error handling -----

    #[Test]
    public function handles_401_authentication_error(): void
    {
        $mock = $this->mockApiClient(
            null,
            new \RuntimeException('Authentication failed. Run covar config set-key <key>')
        );
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Authentication failed',
            $tester->getDisplay()
        );
    }

    #[Test]
    public function handles_403_plan_required_error(): void
    {
        $mock = $this->mockApiClient(
            null,
            new \RuntimeException('API access requires Pro or Enterprise plan')
        );
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'API access requires Pro or Enterprise plan',
            $tester->getDisplay()
        );
    }

    #[Test]
    public function handles_network_error(): void
    {
        $mock = $this->mockApiClient(
            null,
            new \RuntimeException('Network error: unable to reach the CoVa API')
        );
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute([]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Network error: unable to reach the CoVa API',
            $tester->getDisplay()
        );
    }
}
