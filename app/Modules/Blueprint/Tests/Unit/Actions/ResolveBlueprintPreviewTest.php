<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Blueprint\Actions\ResolveBlueprintPreview;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\AiContextTab;
use App\Modules\Blueprint\Tabs\AiContext\Presets\LaravelConventionsPreset;
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
use App\Modules\Blueprint\Tabs\McpServersTab;
use App\Modules\Blueprint\Tabs\TabRegistry;
use App\Modules\Blueprint\Tabs\VscodeExtensionsTab;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResolveBlueprintPreviewTest extends TestCase
{
    private ResolveBlueprintPreview $action;

    protected function setUp(): void
    {
        parent::setUp();

        $presets = new SegmentRegistry;
        $presets->register(new LaravelConventionsPreset);

        $skills = new SegmentRegistry;

        $generator = new AgentGenerator($presets, $skills);

        $registry = new TabRegistry;
        $registry->register(new VscodeExtensionsTab);
        $registry->register(new McpServersTab);
        $registry->register(new AiContextTab($generator));

        $this->action = new ResolveBlueprintPreview($registry);
    }

    #[Test]
    public function resolves_valid_tabs(): void
    {
        $tabsConfig = [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => ['ext1.vscode']]],
        ];

        $outputs = $this->action->execute($tabsConfig);

        $this->assertCount(1, $outputs);
        $this->assertInstanceOf(TabOutput::class, $outputs[0]);
        $this->assertSame('vscode_extensions', $outputs[0]->type->value);
    }

    #[Test]
    public function skips_unknown_tab_types(): void
    {
        $tabsConfig = [
            ['type' => 'unknown_tab_type', 'config' => []],
        ];

        $outputs = $this->action->execute($tabsConfig);

        $this->assertCount(0, $outputs);
    }

    #[Test]
    public function skips_invalid_entries(): void
    {
        $tabsConfig = [
            ['type' => 123, 'config' => []],
            ['type' => 'vscode_extensions', 'config' => ['extensions' => ['ext1.vscode']]],
        ];

        $outputs = $this->action->execute($tabsConfig);

        $this->assertCount(1, $outputs);
    }

    #[Test]
    public function returns_empty_array_for_empty_input(): void
    {
        $outputs = $this->action->execute([]);

        $this->assertSame([], $outputs);
    }

    #[Test]
    public function resolves_multiple_tab_types(): void
    {
        $tabsConfig = [
            ['type' => 'vscode_extensions', 'config' => ['extensions' => ['ext1.vscode']]],
            ['type' => 'mcp_servers', 'config' => ['servers' => [['name' => 'test', 'command' => 'npx', 'args' => []]]]],
            ['type' => 'ai_context', 'config' => ['presets' => ['laravel-conventions'], 'skills' => [], 'custom_rules' => 'Test rule']],
        ];

        $outputs = $this->action->execute($tabsConfig);

        $this->assertCount(3, $outputs);

        $types = array_map(fn (TabOutput $o) => $o->type->value, $outputs);
        $this->assertContains('vscode_extensions', $types);
        $this->assertContains('mcp_servers', $types);
        $this->assertContains('ai_context', $types);
    }

    #[Test]
    public function skips_entries_missing_type_field(): void
    {
        $tabsConfig = [
            ['config' => ['extensions' => ['ext1.vscode']]],
            ['type' => 'vscode_extensions', 'config' => ['extensions' => ['ext2.vscode']]],
        ];

        $outputs = $this->action->execute($tabsConfig);

        $this->assertCount(1, $outputs);
        $this->assertSame('vscode_extensions', $outputs[0]->type->value);
    }
}
