<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\AiContext;

use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Presets\CleanArchitecturePreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\LaravelConventionsPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\PSR12Preset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\SOLIDPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\TypeScriptStrictPreset;
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
        $this->presetsRegistry = new SegmentRegistry();
        $this->presetsRegistry->register(new PSR12Preset());
        $this->presetsRegistry->register(new SOLIDPreset());
        $this->presetsRegistry->register(new CleanArchitecturePreset());
        $this->presetsRegistry->register(new LaravelConventionsPreset());
        $this->presetsRegistry->register(new TypeScriptStrictPreset());

        $this->skillsRegistry = new SegmentRegistry();
        $this->skillsRegistry->register(new StripeSkill());
        $this->skillsRegistry->register(new TailwindSkill());
        $this->skillsRegistry->register(new ReactExpertSkill());
        $this->skillsRegistry->register(new VueExpertSkill());

        $this->generator = new AgentGenerator(
            $this->presetsRegistry,
            $this->skillsRegistry,
        );
    }

    public function test_generate_returns_empty_string_for_empty_config(): void
    {
        $config = new AiContextConfig();

        $result = $this->generator->generate($config);

        $this->assertEquals('', $result);
    }

    public function test_generate_includes_title(): void
    {
        $config = new AiContextConfig(
            presets: ['psr12'],
            skills: [],
            customRules: '',
        );

        $result = $this->generator->generate($config);

        $this->assertStringStartsWith('# Agent Context', $result);
    }

    public function test_generate_includes_requested_presets(): void
    {
        $config = new AiContextConfig(
            presets: ['psr12', 'solid'],
            skills: [],
            customRules: '',
        );

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('PSR-12 Coding Standard', $result);
        $this->assertStringContainsString('SOLID Principles', $result);
    }

    public function test_generate_includes_requested_skills(): void
    {
        $config = new AiContextConfig(
            presets: [],
            skills: ['stripe', 'tailwind'],
            customRules: '',
        );

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('Stripe Integration', $result);
        $this->assertStringContainsString('Tailwind CSS', $result);
    }

    public function test_generate_includes_custom_rules(): void
    {
        $config = new AiContextConfig(
            presets: [],
            skills: [],
            customRules: 'Always use strict types. Prefer DTOs over arrays.',
        );

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('Custom Rules', $result);
        $this->assertStringContainsString('Always use strict types', $result);
    }

    public function test_generate_combines_all_sections(): void
    {
        $config = new AiContextConfig(
            presets: ['psr12'],
            skills: ['stripe'],
            customRules: 'Test rule',
        );

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('PSR-12 Coding Standard', $result);
        $this->assertStringContainsString('Stripe Integration', $result);
        $this->assertStringContainsString('Custom Rules', $result);
        $this->assertStringContainsString('Test rule', $result);
    }

    public function test_generate_skips_unknown_preset(): void
    {
        $config = new AiContextConfig(
            presets: ['unknown-preset'],
            skills: [],
            customRules: '',
        );

        $result = $this->generator->generate($config);

        $this->assertStringStartsWith('# Agent Context', $result);
        $this->assertStringNotContainsString('unknown-preset', $result);
    }

    public function test_generate_skips_unknown_skill(): void
    {
        $config = new AiContextConfig(
            presets: [],
            skills: ['unknown-skill'],
            customRules: '',
        );

        $result = $this->generator->generate($config);

        $this->assertStringStartsWith('# Agent Context', $result);
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
        $config = new AiContextConfig(
            presets: ['laravel-conventions', 'typescript-strict'],
            skills: [],
            customRules: '',
        );

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('Laravel Conventions', $result);
        $this->assertStringContainsString('TypeScript Strict Mode', $result);
    }

    public function test_generate_includes_new_skill_content(): void
    {
        $config = new AiContextConfig(
            presets: [],
            skills: ['react-expert', 'vue-expert'],
            customRules: '',
        );

        $result = $this->generator->generate($config);

        $this->assertStringContainsString('React Expert', $result);
        $this->assertStringContainsString('Vue Expert', $result);
    }

    public function test_preset_names_returns_all_7_presets_after_registration(): void
    {
        // Register the 4 new presets (Phase 5 will create them)
        $this->presetsRegistry->register(
            new \App\Modules\Blueprint\Tabs\AiContext\Presets\DockerPreset()
        );
        $this->presetsRegistry->register(
            new \App\Modules\Blueprint\Tabs\AiContext\Presets\CICDPreset()
        );
        $this->presetsRegistry->register(
            new \App\Modules\Blueprint\Tabs\AiContext\Presets\LaravelConventionsPreset()
        );
        $this->presetsRegistry->register(
            new \App\Modules\Blueprint\Tabs\AiContext\Presets\TypeScriptStrictPreset()
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
        // Register the 3 new skills (Phase 5 will create them)
        $this->skillsRegistry->register(
            new \App\Modules\Blueprint\Tabs\AiContext\Skills\ApiDesignSkill()
        );
        $this->skillsRegistry->register(
            new \App\Modules\Blueprint\Tabs\AiContext\Skills\ReactExpertSkill()
        );
        $this->skillsRegistry->register(
            new \App\Modules\Blueprint\Tabs\AiContext\Skills\VueExpertSkill()
        );

        $names = $this->generator->skillNames();

        $this->assertCount(5, $names);
        $this->assertContains('api-design', $names);
        $this->assertContains('react-expert', $names);
        $this->assertContains('vue-expert', $names);
    }
}
