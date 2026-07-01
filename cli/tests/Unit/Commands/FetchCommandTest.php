<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\ApiClient;
use App\Commands\FetchCommand;
use Illuminate\Container\Container;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class FetchCommandTest extends TestCase
{
    private Container $container;
    private string $tempDir;
    private string $originalCwd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->tempDir = sys_get_temp_dir() . '/cova-fetch-' . bin2hex(random_bytes(4));
        mkdir($this->tempDir, 0755, true);
        $this->originalCwd = getcwd();
        chdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        chdir($this->originalCwd);
        $this->removeDirectory($this->tempDir);

        parent::tearDown();
    }

    /**
     * Remove a directory recursively.
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * Create a blueprint response without secret variables.
     *
     * @return array<string, mixed>
     */
    private function makeBlueprintWithoutSecrets(): array
    {
        return [
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'slug' => 'laravel-api',
            'title' => 'Laravel API Starter',
            'description' => 'A Laravel API starter kit',
            'variables' => [
                [
                    'key' => 'APP_NAME',
                    'type' => 'text',
                    'default_value' => 'MyApp',
                    'is_secret' => false,
                    'section' => null,
                ],
                [
                    'key' => 'APP_ENV',
                    'type' => 'text',
                    'default_value' => 'local',
                    'is_secret' => false,
                    'section' => null,
                ],
            ],
            'agent_md' => "# Agent Context\n\n## Rules\n\nBe helpful.\n\n## Skills\n\nPHP, Laravel.",
            'vscode_extensions' => [
                'bmewburn.vscode-intelephense-client',
                'esbenp.prettier-vscode',
            ],
            'vscode_install_command' => 'code --install-extension bmewburn.vscode-intelephense-client --install-extension esbenp.prettier-vscode',
            'mcp_servers' => [
                'mcp_servers' => [
                    [
                        'name' => 'memory',
                        'command' => 'npx',
                        'args' => ['-y', '@modelcontextprotocol/server-memory'],
                    ],
                ],
            ],
            'scripts' => [],
            'scripts_shell' => '',
        ];
    }

    /**
     * Create a blueprint response WITH secret variables.
     *
     * @return array<string, mixed>
     */
    private function makeBlueprintWithSecrets(): array
    {
        $blueprint = $this->makeBlueprintWithoutSecrets();
        $blueprint['variables'][] = [
            'key' => 'DB_PASSWORD',
            'type' => 'text',
            'default_value' => '',
            'is_secret' => true,
            'section' => null,
        ];
        $blueprint['variables'][] = [
            'key' => 'API_KEY',
            'type' => 'text',
            'default_value' => '',
            'is_secret' => true,
            'section' => null,
        ];

        return $blueprint;
    }

    /**
     * Create a blueprint response with only variables (no tabs).
     *
     * @return array<string, mixed>
     */
    private function makeBlueprintWithOnlyVariables(): array
    {
        return [
            'uuid' => '660e8400-e29b-41d4-a716-446655440001',
            'slug' => 'minimal',
            'title' => 'Minimal',
            'description' => 'No tabs, just env',
            'variables' => [
                ['key' => 'APP_NAME', 'type' => 'text', 'default_value' => 'App', 'is_secret' => false, 'section' => null],
            ],
            'agent_md' => null,
            'vscode_extensions' => [],
            'vscode_install_command' => '',
            'mcp_servers' => [],
            'scripts' => [],
            'scripts_shell' => '',
        ];
    }

    /**
     * Create a mock ApiClient with configurable get/post behaviour.
     */
    private function mockApiClient(
        ?array $getReturn = null,
        ?\RuntimeException $getException = null,
        ?array $postReturn = null,
        ?\RuntimeException $postException = null,
    ): ApiClient {
        $mock = $this->createMock(ApiClient::class);

        if ($getException !== null) {
            $mock->method('get')->willThrowException($getException);
        } else {
            $mock->method('get')->willReturn($getReturn ?? []);
        }

        if ($postException !== null) {
            $mock->method('post')->willThrowException($postException);
        } elseif ($postReturn !== null) {
            $mock->method('post')->willReturn($postReturn);
        }

        $this->container->instance(ApiClient::class, $mock);

        return $mock;
    }

    /**
     * Create a CommandTester for FetchCommand.
     */
    private function createCommandTester(?ApiClient $client = null): CommandTester
    {
        $command = new FetchCommand($client);
        $command->setLaravel($this->container);

        return new CommandTester($command);
    }

    /**
     * Create a CommandTester for a FetchCommand with a mocked promptPassword method.
     *
     * This avoids the Windows hiddeninput.exe blocking behaviour when using
     * $this->secret() in tests.
     */
    private function createCommandTesterWithSecretMock(
        ?ApiClient $client,
        string $returnPassword,
    ): CommandTester {
        $command = $this->getMockBuilder(FetchCommand::class)
            ->setConstructorArgs([$client])
            ->onlyMethods(['promptPassword'])
            ->getMock();

        $command->expects($this->once())
            ->method('promptPassword')
            ->willReturn($returnPassword);

        $command->setLaravel($this->container);

        return new CommandTester($command);
    }

    // ----------------------------------------------------------------
    //  Fetch without secrets
    // ----------------------------------------------------------------

    #[Test]
    public function scaffolds_all_four_files_without_secrets(): void
    {
        $data = $this->makeBlueprintWithoutSecrets();
        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'laravel-api']);

        $this->assertSame(0, $exitCode);

        // Display should show success for each file
        $display = $tester->getDisplay();
        $this->assertStringContainsString('.agent.md', $display);
        $this->assertStringContainsString('.vscode/extensions.json', $display);
        $this->assertStringContainsString('.vscode/mcp.json', $display);
        $this->assertStringContainsString('.env', $display);

        // 1. .agent.md content
        $this->assertFileExists($this->tempDir . '/.agent.md');
        $agentMd = file_get_contents($this->tempDir . '/.agent.md');
        $this->assertStringContainsString('# Agent Context', $agentMd);
        $this->assertStringContainsString('Be helpful', $agentMd);
        $this->assertStringContainsString('## Skills', $agentMd);

        // 2. .vscode/extensions.json content
        $this->assertFileExists($this->tempDir . '/.vscode/extensions.json');
        $extJson = json_decode(file_get_contents($this->tempDir . '/.vscode/extensions.json'), true);
        $this->assertIsArray($extJson);
        $this->assertArrayHasKey('recommendations', $extJson);
        $this->assertContains('bmewburn.vscode-intelephense-client', $extJson['recommendations']);
        $this->assertContains('esbenp.prettier-vscode', $extJson['recommendations']);

        // 3. .vscode/mcp.json content
        $this->assertFileExists($this->tempDir . '/.vscode/mcp.json');
        $mcpJson = json_decode(file_get_contents($this->tempDir . '/.vscode/mcp.json'), true);
        $this->assertIsArray($mcpJson);
        $this->assertArrayHasKey('mcpServers', $mcpJson);
        $this->assertArrayHasKey('memory', $mcpJson['mcpServers']);
        $this->assertSame('npx', $mcpJson['mcpServers']['memory']['command']);
        $this->assertContains('-y', $mcpJson['mcpServers']['memory']['args']);

        // 4. .env content
        $this->assertFileExists($this->tempDir . '/.env');
        $envContent = file_get_contents($this->tempDir . '/.env');
        $this->assertStringContainsString('APP_NAME=MyApp', $envContent);
        $this->assertStringContainsString('APP_ENV=local', $envContent);
    }

    #[Test]
    public function scaffolds_minimal_blueprint_with_only_env(): void
    {
        $data = $this->makeBlueprintWithOnlyVariables();
        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'minimal']);

        $this->assertSame(0, $exitCode);

        // .env should exist
        $this->assertFileExists($this->tempDir . '/.env');
        $this->assertStringContainsString('APP_NAME=App', file_get_contents($this->tempDir . '/.env'));

        // Other files should NOT exist (no content for them)
        $this->assertFileDoesNotExist($this->tempDir . '/.agent.md');
        $this->assertFileDoesNotExist($this->tempDir . '/.vscode/extensions.json');
        $this->assertFileDoesNotExist($this->tempDir . '/.vscode/mcp.json');
    }

    // ----------------------------------------------------------------
    //  Fetch with secrets + correct password
    // ----------------------------------------------------------------

    #[Test]
    public function with_secrets_and_correct_password_writes_decrypted_values(): void
    {
        $data = $this->makeBlueprintWithSecrets();
        $mock = $this->mockApiClient(
            getReturn: $data,
            postReturn: [
                'secrets' => [
                    ['key' => 'DB_PASSWORD', 'value' => 'decrypted_db_pass'],
                    ['key' => 'API_KEY', 'value' => 'sk-12345-secret'],
                ],
            ],
        );
        $tester = $this->createCommandTesterWithSecretMock($mock, 'my-cova-password');
        $exitCode = $tester->execute(['slug' => 'laravel-api']);

        $this->assertSame(0, $exitCode);

        // Display must show success message for secrets
        $display = $tester->getDisplay();
        $this->assertStringContainsString('Secrets decrypted', $display);

        // .env must contain decrypted values
        $envContent = file_get_contents($this->tempDir . '/.env');
        $this->assertStringContainsString('DB_PASSWORD=decrypted_db_pass', $envContent);
        $this->assertStringContainsString('API_KEY=sk-12345-secret', $envContent);

        // Non-secret values must still be present
        $this->assertStringContainsString('APP_NAME=MyApp', $envContent);
    }

    // ----------------------------------------------------------------
    //  Fetch with secrets + wrong password
    // ----------------------------------------------------------------

    #[Test]
    public function with_secrets_and_wrong_password_shows_warning_and_empty_values(): void
    {
        $data = $this->makeBlueprintWithSecrets();
        $mock = $this->mockApiClient(
            getReturn: $data,
            postException: new \RuntimeException('Password verification failed'),
        );
        $tester = $this->createCommandTesterWithSecretMock($mock, 'wrong-password');
        $exitCode = $tester->execute(['slug' => 'laravel-api']);

        $this->assertSame(0, $exitCode);

        // Display must show warning
        $display = $tester->getDisplay();
        $this->assertStringContainsString('Password verification failed', $display);
        $this->assertStringContainsString('fill them manually', $display);

        // .env must have secret variables with EMPTY values
        $envContent = file_get_contents($this->tempDir . '/.env');
        $this->assertStringContainsString('DB_PASSWORD=', $envContent);
        $this->assertStringContainsString('API_KEY=', $envContent);

        // Non-secret must still have values
        $this->assertStringContainsString('APP_NAME=MyApp', $envContent);
    }

    // ----------------------------------------------------------------
    //  Error handling
    // ----------------------------------------------------------------

    #[Test]
    public function adapts_404_to_blueprint_not_found_message(): void
    {
        $mock = $this->mockApiClient(
            getException: new \RuntimeException('Not found'),
        );
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'no-such-blueprint']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Blueprint not found: no-such-blueprint',
            $tester->getDisplay()
        );
    }

    #[Test]
    public function handles_401_authentication_error(): void
    {
        $mock = $this->mockApiClient(
            getException: new \RuntimeException('Authentication failed. Run cova config:set-key <key>'),
        );
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'test']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Authentication failed',
            $tester->getDisplay()
        );
    }

    #[Test]
    public function handles_403_plan_error(): void
    {
        $mock = $this->mockApiClient(
            getException: new \RuntimeException('API access requires Pro or Enterprise plan'),
        );
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'test']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'API access requires Pro or Enterprise plan',
            $tester->getDisplay()
        );
    }

    #[Test]
    public function fetches_blueprint_with_no_variables(): void
    {
        $data = [
            'uuid' => '770e8400-e29b-41d4-a716-446655440002',
            'slug' => 'empty',
            'title' => 'No Vars',
            'description' => 'Has no variables',
            'variables' => [],
            'agent_md' => '# Just agent',
            'vscode_extensions' => [],
            'vscode_install_command' => '',
            'mcp_servers' => [],
            'scripts' => [],
            'scripts_shell' => '',
        ];

        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'empty']);

        $this->assertSame(0, $exitCode);

        // Only .agent.md and .env should be created
        $this->assertFileExists($this->tempDir . '/.agent.md');
        $this->assertFileExists($this->tempDir . '/.env');
        $this->assertFileDoesNotExist($this->tempDir . '/.vscode/extensions.json');
        $this->assertFileDoesNotExist($this->tempDir . '/.vscode/mcp.json');

        // .env should be empty
        $this->assertSame("", trim(file_get_contents($this->tempDir . '/.env')));
    }

    #[Test]
    public function handles_network_error(): void
    {
        $mock = $this->mockApiClient(
            getException: new \RuntimeException('Network error: unable to reach the CoVa API'),
        );
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'test']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Network error: unable to reach the CoVa API',
            $tester->getDisplay()
        );
    }
}
