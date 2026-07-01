<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\ApiClient;
use App\Commands\ConfigSetKeyCommand;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigSetKeyCommandTest extends TestCase
{
    private string $tempHome;
    private string $configDir;
    private string $configPath;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary home directory for config isolation
        $this->tempHome = sys_get_temp_dir() . '/covar-test-' . bin2hex(random_bytes(4));
        $this->configDir = $this->tempHome . '/.config/covar';
        $this->configPath = $this->configDir . '/config.json';

        mkdir($this->configDir, 0755, true);

        // Override HOME so the command writes to the temp directory
        putenv('HOME=' . $this->tempHome);

        // Create a fresh container for each test
        $this->container = new Container();
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        if (is_dir($this->tempHome)) {
            $this->removeDirectory($this->tempHome);
        }

        putenv('HOME');

        parent::tearDown();
    }

    /**
     * Remove a directory recursively.
     */
    private function removeDirectory(string $dir): void
    {
        $files = array_diff(scandir($dir) ?: [], ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * Create a mock ApiClient and bind it in the container.
     */
    private function mockApiClient(bool $connectivityResult): ApiClient
    {
        $mock = $this->createMock(ApiClient::class);
        $mock->method('validateConnectivity')->willReturn($connectivityResult);

        $this->container->instance(ApiClient::class, $mock);

        return $mock;
    }

    /**
     * Create the command tester for ConfigSetKeyCommand.
     */
    private function createCommandTester(?ApiClient $client = null): CommandTester
    {
        $command = new ConfigSetKeyCommand($client);
        $command->setLaravel($this->container);

        return new CommandTester($command);
    }

    #[Test]
    public function valid_key_saves_config_and_confirms(): void
    {
        $mockClient = $this->mockApiClient(true);
        $tester = $this->createCommandTester($mockClient);

        $exitCode = $tester->execute(['key' => 'covar_valid123']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString(
            'API key saved and verified',
            $tester->getDisplay()
        );

        // Verify config file was created with the correct key
        $this->assertFileExists($this->configPath);

        $config = json_decode(file_get_contents($this->configPath), true);

        $this->assertIsArray($config);
        $this->assertSame('covar_valid123', $config['api_key']);
        $this->assertSame('https://api.cova.app', $config['base_url']);
    }

    #[Test]
    public function invalid_key_is_rejected_and_config_not_saved(): void
    {
        $mockClient = $this->mockApiClient(false);
        $tester = $this->createCommandTester($mockClient);

        $exitCode = $tester->execute(['key' => 'covar_invalid']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Invalid API key or token expired',
            $tester->getDisplay()
        );

        // Config file should NOT exist
        $this->assertFileDoesNotExist($this->configPath);
    }

    #[Test]
    public function invalid_key_does_not_overwrite_existing_config(): void
    {
        // Create an existing config file with a valid key
        file_put_contents($this->configPath, json_encode([
            'base_url' => 'https://api.cova.app',
            'api_key' => 'covar_existing_valid',
        ]));

        $mockClient = $this->mockApiClient(false);
        $tester = $this->createCommandTester($mockClient);

        $exitCode = $tester->execute(['key' => 'covar_invalid']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Invalid API key or token expired',
            $tester->getDisplay()
        );

        // Existing config should remain untouched
        $config = json_decode(file_get_contents($this->configPath), true);

        $this->assertSame('covar_existing_valid', $config['api_key']);
    }

    #[Test]
    public function preserves_existing_base_url_when_saving_new_key(): void
    {
        // Create existing config with custom base_url
        file_put_contents($this->configPath, json_encode([
            'base_url' => 'https://custom.cova.app',
            'api_key' => 'covar_old',
        ]));

        $mockClient = $this->mockApiClient(true);
        $tester = $this->createCommandTester($mockClient);

        $exitCode = $tester->execute(['key' => 'covar_new']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString(
            'API key saved and verified',
            $tester->getDisplay()
        );

        // base_url should be preserved, key should be updated
        $config = json_decode(file_get_contents($this->configPath), true);

        $this->assertSame('covar_new', $config['api_key']);
        $this->assertSame('https://custom.cova.app', $config['base_url']);
    }

    #[Test]
    public function accepts_base_url_option(): void
    {
        $mockClient = $this->mockApiClient(true);
        $tester = $this->createCommandTester($mockClient);

        $exitCode = $tester->execute([
            'key' => 'covar_valid',
            '--base-url' => 'https://staging.cova.app',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString(
            'API key saved and verified',
            $tester->getDisplay()
        );

        $config = json_decode(file_get_contents($this->configPath), true);

        $this->assertSame('https://staging.cova.app', $config['base_url']);
    }

    #[Test]
    public function config_file_has_restricted_permissions_on_unix(): void
    {
        $mockClient = $this->mockApiClient(true);
        $tester = $this->createCommandTester($mockClient);

        $exitCode = $tester->execute(['key' => 'covar_valid123']);

        $this->assertSame(0, $exitCode);

        // Only check permissions on non-Windows systems
        if (DIRECTORY_SEPARATOR !== '\\') {
            $perms = fileperms($this->configPath) & 0777;
            $this->assertSame(0600, $perms, 'Config file must have 0600 permissions');
        }

        $this->assertTrue(true);
    }

    #[Test]
    public function network_error_shows_invalid_key_message(): void
    {
        $mockClient = $this->mockApiClient(false);
        $tester = $this->createCommandTester($mockClient);

        $exitCode = $tester->execute(['key' => 'covar_somekey']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Invalid API key or token expired',
            $tester->getDisplay()
        );
    }
}
