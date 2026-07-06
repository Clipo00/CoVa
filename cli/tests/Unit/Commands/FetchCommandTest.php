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
        $this->tempDir = sys_get_temp_dir() . '/covar-fetch-' . bin2hex(random_bytes(4));
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
     * Create a blueprint response with ai_context_segments (new format).
     *
     * @return array<string, mixed>
     */
    private function makeBlueprintWithSegments(): array
    {
        return [
            'uuid' => '880e8400-e29b-41d4-a716-446655440003',
            'slug' => 'modern-stack',
            'title' => 'Modern Stack',
            'description' => 'A blueprint with segments',
            'variables' => [
                [
                    'key' => 'APP_NAME',
                    'type' => 'text',
                    'default_value' => 'MyApp',
                    'is_secret' => false,
                    'section' => null,
                ],
            ],
            'agent_md' => null,
            'vscode_extensions' => [],
            'vscode_install_command' => '',
            'mcp_servers' => [],
            'scripts' => [],
            'scripts_shell' => '',
            'ai_context_segments' => [
                [
                    'type' => 'skill',
                    'name' => 'PHP Laravel',
                    'filename' => 'php-laravel.md',
                    'content' => "## PHP Laravel\n\nLaravel best practices for Eloquent, validation, and testing.",
                ],
                [
                    'type' => 'skill',
                    'name' => 'Vue.js SPA',
                    'filename' => 'vue-js-spa.md',
                    'content' => "## Vue.js SPA\n\nComposition API patterns, Pinia store, Vue Router lazy-loading.",
                ],
                [
                    'type' => 'agent',
                    'name' => 'Custom Agent',
                    'filename' => 'custom-agent.md',
                    'content' => "## Custom Agent\n\nYou are a senior full-stack developer. Focus on architecture and testing.\n\nRefer to the skill files for specific framework guidance.",
                ],
            ],
        ];
    }

    /**
     * Create a blueprint response with duplicate segment names.
     *
     * @return array<string, mixed>
     */
    private function makeBlueprintWithDuplicateSegments(): array
    {
        return [
            'uuid' => '990e8400-e29b-41d4-a716-446655440004',
            'slug' => 'dupes',
            'title' => 'Duplicate Segments',
            'description' => 'Has duplicate names',
            'variables' => [],
            'agent_md' => null,
            'vscode_extensions' => [],
            'vscode_install_command' => '',
            'mcp_servers' => [],
            'scripts' => [],
            'scripts_shell' => '',
            'ai_context_segments' => [
                [
                    'type' => 'skill',
                    'name' => 'PHP',
                    'filename' => 'php.md',
                    'content' => "## PHP\n\nVersion one.",
                ],
                [
                    'type' => 'skill',
                    'name' => 'PHP',
                    'filename' => 'php.md',
                    'content' => "## PHP\n\nVersion two — should be skipped.",
                ],
            ],
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
        $command = new FetchCommand(); $command->setApiClient($client);
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
        $tester = $this->createCommandTesterWithSecretMock($mock, 'my-covar-password');
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
            getException: new \RuntimeException('Authentication failed. Run covar config:set-key <key>'),
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
            getException: new \RuntimeException('Network error: unable to reach the CoVaR API'),
        );
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'test']);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            'Network error: unable to reach the CoVaR API',
            $tester->getDisplay()
        );
    }

    // ----------------------------------------------------------------
    //  Fetch with ai_context_segments (new format)
    // ----------------------------------------------------------------

    #[Test]
    public function scaffolds_agents_directory_with_segments(): void
    {
        $data = $this->makeBlueprintWithSegments();
        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'modern-stack']);

        $this->assertSame(0, $exitCode);

        // Directory structure
        $this->assertDirectoryExists($this->tempDir . '/.agents');
        $this->assertDirectoryExists($this->tempDir . '/.agents/.skills');

        // agent.md must exist with router table
        $this->assertFileExists($this->tempDir . '/.agents/agent.md');
        $agentMd = file_get_contents($this->tempDir . '/.agents/agent.md');
        $this->assertStringContainsString('# Agent Context', $agentMd);
        $this->assertStringContainsString('## Project Skills', $agentMd);
        $this->assertStringContainsString('| Name | File |', $agentMd);
        $this->assertStringContainsString('| PHP Laravel | `URL_SKILL/php-laravel.md` |', $agentMd);
        $this->assertStringContainsString('| Vue.js SPA | `URL_SKILL/vue-js-spa.md` |', $agentMd);

        // Agent preamble must be included
        $this->assertStringContainsString('## Custom Agent', $agentMd);
        $this->assertStringContainsString('senior full-stack developer', $agentMd);

        // Individual skill files must exist
        $this->assertFileExists($this->tempDir . '/.agents/.skills/php-laravel.md');
        $this->assertStringContainsString(
            'Laravel best practices',
            file_get_contents($this->tempDir . '/.agents/.skills/php-laravel.md'),
        );

        $this->assertFileExists($this->tempDir . '/.agents/.skills/vue-js-spa.md');
        $this->assertStringContainsString(
            'Composition API patterns',
            file_get_contents($this->tempDir . '/.agents/.skills/vue-js-spa.md'),
        );

        // Agent segment must NOT have its own file (it's in the preamble only)
        $this->assertFileDoesNotExist($this->tempDir . '/.agents/.skills/custom-agent.md');

        // Legacy .agent.md must NOT exist
        $this->assertFileDoesNotExist($this->tempDir . '/.agent.md');

        // Display must show success for all files
        $display = $tester->getDisplay();
        $this->assertStringContainsString('.agents/agent.md', $display);
        $this->assertStringContainsString('.agents/.skills/php-laravel.md', $display);
        $this->assertStringContainsString('.agents/.skills/vue-js-spa.md', $display);
    }

    #[Test]
    public function handles_duplicate_segment_names_with_warning(): void
    {
        $data = $this->makeBlueprintWithDuplicateSegments();
        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'dupes']);

        $this->assertSame(0, $exitCode);

        // Only first occurrence should be written
        $this->assertFileExists($this->tempDir . '/.agents/.skills/php.md');
        $this->assertStringContainsString(
            'Version one',
            file_get_contents($this->tempDir . '/.agents/.skills/php.md'),
        );

        // It should contain version one, not version two
        $content = file_get_contents($this->tempDir . '/.agents/.skills/php.md');
        $this->assertStringContainsString('Version one', $content);
        $this->assertStringNotContainsString('Version two — should be skipped', $content);

        // Warning about duplicate must be shown
        $display = $tester->getDisplay();
        $this->assertStringContainsString('Duplicate segment name', $display);
        $this->assertStringContainsString('PHP', $display);
    }

    #[Test]
    public function legacy_fallback_when_no_segments(): void
    {
        // Standard blueprint without ai_context_segments uses legacy .agent.md
        $data = $this->makeBlueprintWithoutSecrets();
        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'laravel-api']);

        $this->assertSame(0, $exitCode);

        // Legacy .agent.md must exist
        $this->assertFileExists($this->tempDir . '/.agent.md');

        // New .agents/ must NOT exist
        $this->assertFileDoesNotExist($this->tempDir . '/.agents');

        $agentMd = file_get_contents($this->tempDir . '/.agent.md');
        $this->assertStringContainsString('# Agent Context', $agentMd);
        $this->assertStringContainsString('Be helpful', $agentMd);
    }

    #[Test]
    public function empty_segments_with_no_agent_md_skips_both(): void
    {
        $data = [
            'uuid' => 'aa0e8400-e29b-41d4-a716-446655440005',
            'slug' => 'no-context',
            'title' => 'No Context',
            'description' => 'No AI config at all',
            'variables' => [],
            'agent_md' => null,
            'vscode_extensions' => [],
            'vscode_install_command' => '',
            'mcp_servers' => [],
            'scripts' => [],
            'scripts_shell' => '',
            'ai_context_segments' => [],
        ];

        $mock = $this->mockApiClient($data);
        $tester = $this->createCommandTester($mock);

        $exitCode = $tester->execute(['slug' => 'no-context']);

        $this->assertSame(0, $exitCode);

        // Neither format should exist
        $this->assertFileDoesNotExist($this->tempDir . '/.agent.md');
        $this->assertFileDoesNotExist($this->tempDir . '/.agents');

        // .env should still be created (empty)
        $this->assertFileExists($this->tempDir . '/.env');
    }
}
