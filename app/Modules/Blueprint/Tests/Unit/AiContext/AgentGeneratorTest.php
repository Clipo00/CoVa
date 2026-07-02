<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\AiContext;

use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\DTOs\AiContextSegment;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\Agents\AgentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Presets\CICDPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\CleanArchitecturePreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\DockerPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\LaravelConventionsPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\PSR12Preset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\SOLIDPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\TypeScriptStrictPreset;
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

    private SegmentRegistry $presetsRegistry;

    private SegmentRegistry $skillsRegistry;

    protected function setUp(): void
    {
        $this->presetsRegistry = new SegmentRegistry;
        $this->presetsRegistry->register(new PSR12Preset);
        $this->presetsRegistry->register(new SOLIDPreset);
        $this->presetsRegistry->register(new CleanArchitecturePreset);
        $this->presetsRegistry->register(new LaravelConventionsPreset);
        $this->presetsRegistry->register(new TypeScriptStrictPreset);

        $this->skillsRegistry = new SegmentRegistry;
        $this->skillsRegistry->register(new StripeSkill);
        $this->skillsRegistry->register(new TailwindSkill);
        $this->skillsRegistry->register(new ReactExpertSkill);
        $this->skillsRegistry->register(new VueExpertSkill);

        $agentsRegistry = new AgentRegistry;

        $this->generator = new AgentGenerator(
            $this->presetsRegistry,
            $this->skillsRegistry,
            $agentsRegistry,
        );
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
            new AiContextSegment(type: 'preset', name: 'psr12'),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringStartsWith('# Agent Context', $result);
    }

    public function test_generate_includes_preset_registry_content(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'preset', name: 'psr12'),
            new AiContextSegment(type: 'preset', name: 'solid'),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('PSR-12 Coding Standard', $result);
        $this->assertStringContainsString('SOLID Principles', $result);
    }

    public function test_generate_includes_skill_registry_content(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'stripe'),
            new AiContextSegment(type: 'skill', name: 'tailwind'),
        ]);

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('Stripe Integration', $result);
        $this->assertStringContainsString('Tailwind CSS', $result);
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
            new AiContextSegment(type: 'preset', name: 'psr12'),
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
            new AiContextSegment(type: 'preset', name: 'solid'),
            new AiContextSegment(type: 'preset', name: 'laravel-conventions'),
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
                type: 'preset',
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

    public function test_generate_skips_unknown_preset(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'preset', name: 'unknown-preset'),
        ]);

        $result = $this->generator->generate($config);

        // Only the header — unknown segments are skipped
        $this->assertEquals('# Agent Context', $result);
    }

    public function test_generate_skips_unknown_skill(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'skill', name: 'unknown-skill'),
        ]);

        $result = $this->generator->generate($config);

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

    public function test_preset_names_returns_available_presets(): void
    {
        $names = $this->generator->presetNames();

        $this->assertContains('psr12', $names);
        $this->assertContains('solid', $names);
        $this->assertContains('clean-architecture', $names);
        $this->assertContains('laravel-conventions', $names);
        $this->assertContains('typescript-strict', $names);
        $this->assertCount(5, $names);
    }

    public function test_skill_names_returns_available_skills(): void
    {
        $names = $this->generator->skillNames();

        $this->assertContains('stripe', $names);
        $this->assertContains('tailwind', $names);
        $this->assertContains('react-expert', $names);
        $this->assertContains('vue-expert', $names);
        $this->assertCount(4, $names);
    }

    public function test_new_preset_appears_in_registry(): void
    {
        $names = $this->generator->presetNames();

        $this->assertContains('laravel-conventions', $names);
        $this->assertContains('typescript-strict', $names);
    }

    public function test_new_skill_appears_in_registry(): void
    {
        $names = $this->generator->skillNames();

        $this->assertContains('react-expert', $names);
        $this->assertContains('vue-expert', $names);
    }

    public function test_generate_includes_new_preset_content(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'preset', name: 'laravel-conventions'),
            new AiContextSegment(type: 'preset', name: 'typescript-strict'),
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

    public function test_preset_names_returns_all_7_presets_after_registration(): void
    {
        $this->presetsRegistry->register(
            new DockerPreset
        );
        $this->presetsRegistry->register(
            new CICDPreset
        );
        $this->presetsRegistry->register(
            new LaravelConventionsPreset
        );
        $this->presetsRegistry->register(
            new TypeScriptStrictPreset
        );

        $names = $this->generator->presetNames();

        $this->assertCount(7, $names);
        $this->assertContains('docker', $names);
        $this->assertContains('cicd', $names);
        $this->assertContains('laravel-conventions', $names);
        $this->assertContains('typescript-strict', $names);
    }

    public function test_skill_names_returns_all_5_skills_after_registration(): void
    {
        $this->skillsRegistry->register(
            new ApiDesignSkill
        );
        $this->skillsRegistry->register(
            new ReactExpertSkill
        );
        $this->skillsRegistry->register(
            new VueExpertSkill
        );

        $names = $this->generator->skillNames();

        $this->assertCount(5, $names);
        $this->assertContains('api-design', $names);
        $this->assertContains('react-expert', $names);
        $this->assertContains('vue-expert', $names);
    }

    // --- resolveSegments ---

    public function test_resolve_segments_returns_correct_array_shape(): void
    {
        $config = new AiContextConfig(segments: [
            new AiContextSegment(type: 'preset', name: 'psr12'),
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
            new AiContextSegment(type: 'preset', name: 'solid'),
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
