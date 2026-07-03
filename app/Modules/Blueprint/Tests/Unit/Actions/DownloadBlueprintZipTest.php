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
