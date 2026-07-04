<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\AiContext;

use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\DTOs\AiContextSegment;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\Skills\CICDSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\CleanArchitectureSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\DockerSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\LaravelConventionsSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\PSR12Skill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\SOLIDSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\TypeScriptStrictSkill;
use App\Modules\Blueprint\Tabs\AiContext\Agents\AgentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Skills\ApiDesignSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\ReactExpertSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\StripeSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\TailwindSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\VueExpertSkill;
use PHPUnit\Framework\TestCase;

class AgentGeneratorTest extends TestCase
{
    private AgentGenerator $generator;

    private SegmentRegistry $skillsRegistry;

    private AgentRegistry $agentRegistry;

    protected function setUp(): void
    {
        $this->skillsRegistry = new SegmentRegistry;
        $this->skillsRegistry->register(new PSR12Skill);
        $this->skillsRegistry->register(new SOLIDSkill);
        $this->skillsRegistry->register(new CleanArchitectureSkill);
        $this->skillsRegistry->register(new LaravelConventionsSkill);
        $this->skillsRegistry->register(new TypeScriptStrictSkill);
        $this->skillsRegistry->register(new DockerSkill);
        $this->skillsRegistry->register(new CICDSkill);
        $this->skillsRegistry->register(new StripeSkill);
        $this->skillsRegistry->register(new TailwindSkill);
        $this->skillsRegistry->register(new ReactExpertSkill);
        $this->skillsRegistry->register(new VueExpertSkill);
        $this->skillsRegistry->register(new ApiDesignSkill);

        $this->agentRegistry = new AgentRegistry;
        $this->generator = new AgentGenerator($this->skillsRegistry, $this->agentRegistry);
    }

    public function test_generate_returns_empty_string_for_empty_config(): void
    {
        $config = new AiContextConfig;

        $result = $this->generator->generate($config);

        $this->assertEquals('', $result);
    }

    public function test_generate_includes_title(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'psr12'),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringStartsWith('# Agent Context', $result);
    }

    public function test_generate_includes_skill_registry_content(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'psr12'),
            new AiContextSegment(type: 'skill', name: 'solid'),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('PSR-12 Coding Standard', $result);
        $this->assertStringContainsString('SOLID Principles', $result);
    }

    public function test_generate_includes_custom_segment(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(
                type: 'custom',
                name: 'Custom Rules',
                content: 'Always use strict types. Prefer DTOs over arrays.',
            ),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('## Custom Rules', $result);
        $this->assertStringContainsString('Always use strict types', $result);
    }

    public function test_generate_combines_mixed_segments(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'psr12'),
            new AiContextSegment(type: 'skill', name: 'stripe'),
            new AiContextSegment(type: 'custom', name: 'My Rules', content: 'Test rule'),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('PSR-12 Coding Standard', $result);
        $this->assertStringContainsString('Stripe Integration', $result);
        $this->assertStringContainsString('## My Rules', $result);
        $this->assertStringContainsString('Test rule', $result);
    }

    public function test_generate_adds_separators_between_segments(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'solid'),
            new AiContextSegment(type: 'skill', name: 'laravel-conventions'),
        ]);

        $result = $this->generator->generate($config);
        // Segments are separated by "---" when there are multiple
        $parts = explode('---', $result);

        // Header + 2 segments = 3 parts separated by 2 dividers
        $this->assertCount(3, $parts);
    }

    public function test_generate_uses_override_content_when_provided(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(
                type: 'skill',
                name: 'psr12',
                content: 'Custom PSR-12 override content',
            ),
        ]);

        $result = $this->generator->generate($config);

        // Should NOT contain the registry default heading/content
        $this->assertStringNotContainsString('PSR-12 Coding Standard', $result);
        // Should use the generated heading with override content
        $this->assertStringContainsString('## psr12', $result);
        $this->assertStringContainsString('Custom PSR-12 override content', $result);
    }

    public function test_generate_skips_unknown_skill(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'unknown-skill'),
        ]);

        $result = $this->generator->generate($config);

        // Only the header — unknown segments are skipped
        $this->assertEquals('# Agent Context', $result);
    }

    public function test_generate_handles_empty_override_content(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'custom', name: 'Empty Section', content: ''),
        ]);

        $result = $this->generator->generate($config);

        // Empty content produces just the heading
        $this->assertStringContainsString('## Empty Section', $result);
        // No trailing content after heading
        $this->assertStringEndsWith('## Empty Section', trim($result));
    }

    public function test_skill_names_returns_available_skills(): void
    {
        $names = $this->generator->skillNames();

        $this->assertContains('psr12', $names);
        $this->assertContains('solid', $names);
        $this->assertContains('clean-architecture', $names);
        $this->assertContains('docker', $names);
        $this->assertContains('cicd', $names);
        $this->assertContains('api-design', $names);
        $this->assertContains('laravel-conventions', $names);
        $this->assertContains('typescript-strict', $names);
        $this->assertContains('stripe', $names);
        $this->assertContains('tailwind', $names);
        $this->assertContains('react-expert', $names);
        $this->assertContains('vue-expert', $names);
        $this->assertCount(12, $names);
    }

    public function test_generate_includes_additional_skill_content(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'laravel-conventions'),
            new AiContextSegment(type: 'skill', name: 'typescript-strict'),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('Laravel Conventions', $result);
        $this->assertStringContainsString('TypeScript Strict Mode', $result);
    }

    public function test_generate_includes_new_skill_content(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'react-expert'),
            new AiContextSegment(type: 'skill', name: 'vue-expert'),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('React Expert', $result);
        $this->assertStringContainsString('Vue Expert', $result);
    }

    // --- resolveSegments ---

    public function test_resolve_segments_returns_correct_array_shape(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'psr12'),
            new AiContextSegment(type: 'skill', name: 'stripe'),
        ]);

        $segments = $this->generator->resolveSegments($config);

        $this->assertCount(2, $segments);
        $this->assertArrayHasKey('name', $segments[0]);
        $this->assertArrayHasKey('filename', $segments[0]);
        $this->assertArrayHasKey('content', $segments[0]);
        // Filenames must end with .md
        $this->assertStringEndsWith('.md', $segments[0]['filename']);
    }

    public function test_resolve_segments_sanitizes_filenames(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'custom', name: 'My Rules!!! (v2)', content: 'Test'),
        ]);

        $segments = $this->generator->resolveSegments($config);

        $this->assertCount(1, $segments);
        $this->assertSame('my-rules-v2.md', $segments[0]['filename']);
    }

    public function test_resolve_segments_handles_empty_config(): void
    {
        $config = new AiContextConfig;

        $segments = $this->generator->resolveSegments($config);

        $this->assertIsArray($segments);
        $this->assertCount(0, $segments);
    }

    public function test_resolve_segments_generate_produces_matching_output(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'solid'),
            new AiContextSegment(type: 'skill', name: 'tailwind'),
        ]);

        $segments = $this->generator->resolveSegments($config);
        $joined = '# Agent Context';
        foreach ($segments as $seg) {
            $joined .= "\n\n---\n\n".$seg['content'];
        }

        $generated = $this->generator->generate($config);

        $this->assertSame($generated, $joined);
    }

    public function test_resolve_segments_content_includes_heading(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'custom', name: 'My Custom Rules', content: 'Always write tests.'),
        ]);

        $segments = $this->generator->resolveSegments($config);

        $this->assertStringContainsString('## My Custom Rules', $segments[0]['content']);
        $this->assertStringContainsString('Always write tests.', $segments[0]['content']);
    }
}

