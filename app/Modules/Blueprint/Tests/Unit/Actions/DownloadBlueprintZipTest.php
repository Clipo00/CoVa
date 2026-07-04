<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Blueprint\Actions\DownloadBlueprintZip;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\Agents\AgentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Skills\PSR12Skill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\SOLIDSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\StripeSkill;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class DownloadBlueprintZipTest extends TestCase
{
    private AgentGenerator $agentGenerator;

    private DownloadBlueprintZip $action;

    protected function setUp(): void
    {
        parent::setUp();

        $skillsRegistry = new SegmentRegistry;
        $skillsRegistry->register(new PSR12Skill);
        $skillsRegistry->register(new SOLIDSkill);
        $skillsRegistry->register(new StripeSkill);

        $agentRegistry = new AgentRegistry;

        $this->agentGenerator = new AgentGenerator($skillsRegistry, $agentRegistry);
        $this->action = new DownloadBlueprintZip($this->agentGenerator);
    }

    public function test_returns_streamed_response_with_zip_headers(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                            ['type' => 'skill', 'name' => 'solid'],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->action->execute($blueprint);

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals('application/zip', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'attachment; filename="test-bp.zip"',
            $response->headers->get('Content-Disposition'),
        );
    }

    public function test_zip_contains_agents_agent_md(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                            ['type' => 'skill', 'name' => 'stripe'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipContainsFile($zipContent, '.agents/agent.md');
    }

    public function test_zip_contains_skill_files_in_agents_skills_directory(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                            ['type' => 'skill', 'name' => 'solid'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipContainsFile($zipContent, '.agents/.skills/psr12.md');
        $this->assertZipContainsFile($zipContent, '.agents/.skills/solid.md');
    }

    public function test_agent_md_contains_project_skills_router_table(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                            ['type' => 'skill', 'name' => 'solid'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $agentMd = $this->getZipFileContent($zipContent, '.agents/agent.md');

        $this->assertStringContainsString('# Agent Context', $agentMd);
        $this->assertStringContainsString('## Project Skills', $agentMd);
        $this->assertStringContainsString('URL_SKILL/psr12.md', $agentMd);
        $this->assertStringContainsString('URL_SKILL/solid.md', $agentMd);
    }

    public function test_agent_md_description_comes_from_first_heading_when_available(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $agentMd = $this->getZipFileContent($zipContent, '.agents/agent.md');

        // PSR-12 content starts with "## PSR-12 Coding Standard" — use that as description
        $this->assertStringContainsString('PSR-12 Coding Standard', $agentMd);
        $this->assertStringContainsString('URL_SKILL/psr12.md', $agentMd);
    }

    public function test_agent_md_uses_segment_name_as_fallback_when_content_has_no_heading(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'custom', 'name' => 'my-rules', 'content' => 'Just some text without heading'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $agentMd = $this->getZipFileContent($zipContent, '.agents/agent.md');

        // Custom segments use generated heading "## my-rules"
        // But since we generate "## my-rules" as the heading, the description becomes "my-rules"
        // (the heading IS the segment name when content has no explicit ## heading)
        $this->assertStringContainsString('my-rules', $agentMd);
    }

    public function test_includes_custom_segments_in_zip(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                            ['type' => 'custom', 'name' => 'My Rules', 'content' => 'Always use strict types.'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipContainsFile($zipContent, '.agents/.skills/psr12.md');
        $this->assertZipContainsFile($zipContent, '.agents/.skills/my-rules.md');
    }

    public function test_custom_segment_content_is_rendered_in_skill_file(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'custom', 'name' => 'My Rules', 'content' => 'Always use strict types.'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $skillContent = $this->getZipFileContent($zipContent, '.agents/.skills/my-rules.md');

        $this->assertStringContainsString('Always use strict types.', $skillContent);
    }

    public function test_does_not_create_skill_files_for_agent_segments(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'solid'],
                            ['type' => 'agent', 'name' => 'laravel-developer', 'content' => '## Laravel Developer Agent'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        // agent segment should NOT have individual file
        $this->assertZipContainsFile($zipContent, '.agents/.skills/solid.md');
        $this->assertZipDoesNotContainFile($zipContent, '.agents/.skills/laravel-developer.md');

        // Agent preamble should appear in agent.md after the router table
        $agentMd = $this->getZipFileContent($zipContent, '.agents/agent.md');
        $this->assertStringContainsString('Laravel Developer Agent', $agentMd);

        // Agent content should be AFTER the table, not mixed in
        $tablePos = strpos($agentMd, '## Project Skills');
        $agentPos = strpos($agentMd, 'Laravel Developer Agent');
        $this->assertNotFalse($tablePos);
        $this->assertNotFalse($agentPos);
        $this->assertLessThan($agentPos, $tablePos);
    }

    public function test_handles_blueprint_without_ai_context_tab(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'empty-bp',
            'tabs_config' => [
                ['type' => 'vscode_extensions', 'config' => ['extensions' => []]],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $agentMd = $this->getZipFileContent($zipContent, '.agents/agent.md');

        // No segments means: # Agent Context, ## Project Skills (empty table), no extra rows
        $this->assertStringContainsString('# Agent Context', $agentMd);
        $this->assertStringContainsString('## Project Skills', $agentMd);
    }

    public function test_handles_empty_tabs_config(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'empty-bp',
            'tabs_config' => [],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $agentMd = $this->getZipFileContent($zipContent, '.agents/agent.md');
        $this->assertStringContainsString('# Agent Context', $agentMd);
        $this->assertStringContainsString('## Project Skills', $agentMd);
    }

    public function test_handles_null_tabs_config(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'empty-bp',
            'tabs_config' => null,
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $agentMd = $this->getZipFileContent($zipContent, '.agents/agent.md');
        $this->assertStringContainsString('# Agent Context', $agentMd);
        $this->assertStringContainsString('## Project Skills', $agentMd);
    }

    public function test_streamed_response_content_is_valid_zip(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                            ['type' => 'custom', 'name' => 'rules', 'content' => 'Some rules.'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $tempFile = tempnam(sys_get_temp_dir(), 'zip-valid-');
        file_put_contents($tempFile, $zipContent);

        try {
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($tempFile), 'ZIP file should be valid');
            $this->assertGreaterThanOrEqual(2, $zip->numFiles);
            $zip->close();
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function test_skill_file_content_matches_resolved_content(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'custom', 'name' => 'My Rule', 'content' => 'Always write tests.'],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $skillContent = $this->getZipFileContent($zipContent, '.agents/.skills/my-rule.md');

        $this->assertStringContainsString('## My Rule', $skillContent);
        $this->assertStringContainsString('Always write tests.', $skillContent);
    }

    // --- New tests for full blueprint ZIP ---

    public function test_has_secrets_returns_true_when_secrets_exist(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [],
            'variables' => Collection::make([
                (object) ['key' => 'APP_NAME', 'default_value' => 'MyApp', 'is_secret' => false, 'section' => 'General', 'sort_order' => 0],
                (object) ['key' => 'API_KEY', 'default_value' => 'abc123', 'is_secret' => true, 'section' => 'Secrets', 'sort_order' => 1],
            ]),
        ]);

        $this->assertTrue($this->action->hasSecrets($blueprint));
    }

    public function test_has_secrets_returns_false_when_no_secrets(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [],
            'variables' => Collection::make([
                (object) ['key' => 'APP_NAME', 'default_value' => 'MyApp', 'is_secret' => false, 'section' => 'General', 'sort_order' => 0],
            ]),
        ]);

        $this->assertFalse($this->action->hasSecrets($blueprint));
    }

    public function test_has_secrets_returns_false_when_no_variables(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [],
            'variables' => Collection::make([]),
        ]);

        $this->assertFalse($this->action->hasSecrets($blueprint));
    }

    public function test_generate_password_length_and_format(): void
    {
        $password = $this->action->generatePassword();

        $this->assertEquals(32, strlen($password));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $password);
    }

    public function test_generate_password_is_random(): void
    {
        $password1 = $this->action->generatePassword();
        $password2 = $this->action->generatePassword();

        $this->assertNotEquals($password1, $password2);
    }

    public function test_plain_zip_contains_env_file(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [],
            'variables' => Collection::make([
                (object) ['key' => 'APP_NAME', 'default_value' => 'MyApp', 'is_secret' => false, 'section' => 'General', 'sort_order' => 0],
                (object) ['key' => 'DB_HOST', 'default_value' => 'localhost', 'is_secret' => false, 'section' => 'Database', 'sort_order' => 0],
            ]),
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipContainsFile($zipContent, '.env');
    }

    public function test_plain_zip_env_content_format(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [],
            'variables' => Collection::make([
                (object) ['key' => 'APP_NAME', 'default_value' => 'MyApp', 'is_secret' => false, 'section' => 'General', 'sort_order' => 0],
                (object) ['key' => 'DB_HOST', 'default_value' => 'localhost', 'is_secret' => false, 'section' => 'Database', 'sort_order' => 0],
            ]),
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $envContent = $this->getZipFileContent($zipContent, '.env');

        $this->assertStringContainsString('# --- General ---', $envContent);
        $this->assertStringContainsString('APP_NAME=MyApp', $envContent);
        $this->assertStringContainsString('# --- Database ---', $envContent);
        $this->assertStringContainsString('DB_HOST=localhost', $envContent);
    }

    public function test_build_env_content_secrets_are_blank_when_not_included(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [],
            'variables' => Collection::make([
                (object) ['key' => 'API_KEY', 'default_value' => 'real-secret-value', 'is_secret' => true, 'section' => 'Secrets', 'sort_order' => 0],
            ]),
        ]);

        $content = $this->action->buildEnvContent($blueprint, false);

        $this->assertStringContainsString('API_KEY=', $content);
        $this->assertStringNotContainsString('real-secret-value', $content);
    }

    public function test_build_env_content_includes_secrets_when_flag_is_true(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [],
            'variables' => Collection::make([
                (object) ['key' => 'API_KEY', 'default_value' => 'real-secret-value', 'is_secret' => true, 'section' => 'Secrets', 'sort_order' => 0],
            ]),
        ]);

        $content = $this->action->buildEnvContent($blueprint, true);

        $this->assertStringContainsString('API_KEY=real-secret-value', $content);
    }

    public function test_plain_zip_contains_mcp_servers_json(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                [
                    'type' => 'mcp_servers',
                    'config' => [
                        'servers' => [
                            ['name' => 'filesystem', 'command' => 'npx', 'args' => ['-y', '@modelcontextprotocol/server-filesystem']],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipContainsFile($zipContent, '.mcp/servers.json');
    }

    public function test_plain_zip_mcp_servers_json_content(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                [
                    'type' => 'mcp_servers',
                    'config' => [
                        'servers' => [
                            ['name' => 'filesystem', 'command' => 'npx', 'args' => ['-y', '@modelcontextprotocol/server-filesystem']],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $jsonContent = $this->getZipFileContent($zipContent, '.mcp/servers.json');
        $decoded = json_decode($jsonContent, true);

        $this->assertIsArray($decoded);
        $this->assertCount(1, $decoded);
        $this->assertEquals('filesystem', $decoded[0]['name']);
        $this->assertEquals('npx', $decoded[0]['command']);
        $this->assertEquals(['-y', '@modelcontextprotocol/server-filesystem'], $decoded[0]['args']);
    }

    public function test_plain_zip_contains_vscode_extensions_json(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                [
                    'type' => 'vscode_extensions',
                    'config' => [
                        'extensions' => ['esbenp.prettier-vscode', 'dbaeumer.vscode-eslint'],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipContainsFile($zipContent, '.vscode/extensions.json');
    }

    public function test_plain_zip_vscode_extensions_json_content(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                [
                    'type' => 'vscode_extensions',
                    'config' => [
                        'extensions' => ['esbenp.prettier-vscode', 'dbaeumer.vscode-eslint'],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $jsonContent = $this->getZipFileContent($zipContent, '.vscode/extensions.json');
        $decoded = json_decode($jsonContent, true);

        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded);
        $this->assertContains('esbenp.prettier-vscode', $decoded);
        $this->assertContains('dbaeumer.vscode-eslint', $decoded);
    }

    public function test_plain_zip_contains_scripts_install_sh(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                [
                    'type' => 'scripts',
                    'config' => [
                        'scripts' => [
                            ['command' => 'composer install', 'description' => 'Install PHP dependencies', 'order' => 0],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipContainsFile($zipContent, 'scripts/install.sh');
    }

    public function test_plain_zip_scripts_install_sh_content(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                [
                    'type' => 'scripts',
                    'config' => [
                        'scripts' => [
                            ['command' => 'composer install', 'description' => 'Install PHP dependencies', 'order' => 0],
                            ['command' => 'npm install', 'description' => 'Install Node dependencies', 'order' => 1],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $scriptContent = $this->getZipFileContent($zipContent, 'scripts/install.sh');

        $this->assertStringContainsString('#!/bin/bash', $scriptContent);
        $this->assertStringContainsString('# Install PHP dependencies', $scriptContent);
        $this->assertStringContainsString('composer install', $scriptContent);
        $this->assertStringContainsString('# Install Node dependencies', $scriptContent);
        $this->assertStringContainsString('npm install', $scriptContent);
    }

    public function test_empty_mcp_tab_skipped(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                ['type' => 'mcp_servers', 'config' => ['servers' => []]],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipDoesNotContainFile($zipContent, '.mcp/servers.json');
    }

    public function test_empty_vscode_tab_skipped(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                ['type' => 'vscode_extensions', 'config' => ['extensions' => []]],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipDoesNotContainFile($zipContent, '.vscode/extensions.json');
    }

    public function test_empty_scripts_tab_skipped(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                ['type' => 'ai_context', 'config' => ['segments' => []]],
                ['type' => 'scripts', 'config' => ['scripts' => []]],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        $this->assertZipDoesNotContainFile($zipContent, 'scripts/install.sh');
    }

    public function test_existing_agent_behavior_still_works_with_new_assets(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [
                [
                    'type' => 'ai_context',
                    'config' => [
                        'segments' => [
                            ['type' => 'skill', 'name' => 'psr12'],
                        ],
                    ],
                ],
                [
                    'type' => 'mcp_servers',
                    'config' => [
                        'servers' => [
                            ['name' => 'filesystem', 'command' => 'npx', 'args' => []],
                        ],
                    ],
                ],
            ],
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));

        // Existing behavior
        $this->assertZipContainsFile($zipContent, '.agents/agent.md');
        $this->assertZipContainsFile($zipContent, '.agents/.skills/psr12.md');

        // New assets
        $this->assertZipContainsFile($zipContent, '.mcp/servers.json');
    }

    public function test_encrypted_zip_is_encrypted(): void
    {
        $blueprint = $this->mockBlueprint([
            'slug' => 'test-bp',
            'tabs_config' => [],
            'variables' => Collection::make([
                (object) ['key' => 'API_KEY', 'default_value' => 'real-secret-value', 'is_secret' => true, 'section' => 'Secrets', 'sort_order' => 0],
            ]),
        ]);

        $zipContent = $this->captureZipContent($this->action->execute($blueprint));
        $tempFile = tempnam(sys_get_temp_dir(), 'zip-enc-');
        file_put_contents($tempFile, $zipContent);

        try {
            $zip = new ZipArchive;
            if (!$zip->open($tempFile)) {
                $this->fail('Could not open encrypted ZIP');
            }

            // Without password, reading an encrypted entry should fail
            $content = @$zip->getFromName('.env');
            $this->assertFalse($content, 'Expected .env to be encrypted (reading without password should fail)');

            $zip->close();
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    // --- Helper methods ---

    private function mockBlueprint(array $attributes): Blueprint
    {
        $blueprint = $this->createMock(Blueprint::class);
        $blueprint->method('__get')->willReturnCallback(function (string $name) use ($attributes) {
            return $attributes[$name] ?? null;
        });
        $blueprint->method('__isset')->willReturnCallback(function (string $name) use ($attributes) {
            return isset($attributes[$name]) || $name === 'variables';
        });

        return $blueprint;
    }

    private function captureZipContent(StreamedResponse $response): string
    {
        ob_start();
        $response->sendContent();

        return ob_get_clean();
    }

    private function assertZipContainsFile(string $zipContent, string $expectedPath): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'zip-test-');
        file_put_contents($tempFile, $zipContent);

        try {
            $zip = new ZipArchive;
            if (!$zip->open($tempFile)) {
                $this->fail('Could not open ZIP content for assertion');
            }

            $this->assertNotFalse(
                $zip->locateName($expectedPath),
                "Expected ZIP to contain file: {$expectedPath}",
            );

            $zip->close();
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    private function assertZipDoesNotContainFile(string $zipContent, string $unexpectedPath): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'zip-test-');
        file_put_contents($tempFile, $zipContent);

        try {
            $zip = new ZipArchive;
            if (!$zip->open($tempFile)) {
                $this->fail('Could not open ZIP content for assertion');
            }

            $this->assertFalse(
                $zip->locateName($unexpectedPath),
                "Expected ZIP NOT to contain file: {$unexpectedPath}",
            );

            $zip->close();
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    private function getZipFileContent(string $zipContent, string $path): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'zip-test-');
        file_put_contents($tempFile, $zipContent);

        try {
            $zip = new ZipArchive;
            if (!$zip->open($tempFile)) {
                $this->fail('Could not open ZIP content for reading');
            }

            $content = $zip->getFromName($path);
            $this->assertNotFalse($content, "Could not read {$path} from ZIP");

            $zip->close();

            return $content;
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
