<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Tabs;

use App\Modules\Blueprint\DTOs\TabConfig;
use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Exceptions\UnknownTabTypeException;
use App\Modules\Blueprint\Tabs\TabRegistry;
use App\Modules\Blueprint\Tabs\ScriptsTab;
use App\Modules\Blueprint\Tabs\VscodeExtensionsTab;
use App\Modules\Blueprint\Tabs\McpServersTab;
use App\Modules\Blueprint\Tabs\AiContext\AiContextTab;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Presets\PSR12Preset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\SOLIDPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\CleanArchitecturePreset;
use App\Modules\Blueprint\Tabs\AiContext\Skills\StripeSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\TailwindSkill;
use PHPUnit\Framework\TestCase;

class TabRegistryTest extends TestCase
{
    private function createRegistry(): TabRegistry
    {
        $presetsRegistry = new SegmentRegistry();
        $presetsRegistry->register(new PSR12Preset());
        $presetsRegistry->register(new SOLIDPreset());
        $presetsRegistry->register(new CleanArchitecturePreset());

        $skillsRegistry = new SegmentRegistry();
        $skillsRegistry->register(new StripeSkill());
        $skillsRegistry->register(new TailwindSkill());

        $agentGenerator = new AgentGenerator($presetsRegistry, $skillsRegistry);

        $registry = new TabRegistry();
        $registry->register(new VscodeExtensionsTab());
        $registry->register(new McpServersTab());
        $registry->register(new ScriptsTab());
        $registry->register(new AiContextTab($agentGenerator));

        return $registry;
    }

    public function test_has_returns_true_for_registered_tab(): void
    {
        $registry = $this->createRegistry();

        $this->assertTrue($registry->has('vscode_extensions'));
        $this->assertTrue($registry->has('mcp_servers'));
        $this->assertTrue($registry->has('scripts'));
        $this->assertTrue($registry->has('ai_context'));
    }

    public function test_has_returns_false_for_unregistered_tab(): void
    {
        $registry = $this->createRegistry();

        $this->assertFalse($registry->has('unknown_tab'));
    }

    public function test_get_returns_tab_instance(): void
    {
        $registry = $this->createRegistry();

        $tab = $registry->get('vscode_extensions');

        $this->assertInstanceOf(VscodeExtensionsTab::class, $tab);
    }

    public function test_get_throws_exception_for_unknown_tab(): void
    {
        $registry = $this->createRegistry();

        $this->expectException(UnknownTabTypeException::class);
        $registry->get('unknown_tab');
    }

    public function test_types_returns_all_registered_types(): void
    {
        $registry = $this->createRegistry();

        $types = $registry->types();

        $this->assertCount(4, $types);
        $this->assertContains('vscode_extensions', $types);
        $this->assertContains('mcp_servers', $types);
        $this->assertContains('scripts', $types);
        $this->assertContains('ai_context', $types);
    }
}
