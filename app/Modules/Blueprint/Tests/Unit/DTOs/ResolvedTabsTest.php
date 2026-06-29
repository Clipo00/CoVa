<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\DTOs;

use App\Modules\Blueprint\DTOs\ResolvedTabs;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResolvedTabsTest extends TestCase
{
    /** @return TabOutput[] */
    private function makeTabs(): array
    {
        return [
            new TabOutput(
                type: TabType::AI_CONTEXT,
                content: "# Agent Context\n\nSome markdown content.",
            ),
            new TabOutput(
                type: TabType::VSCODE_EXTENSIONS,
                content: ['extensions' => ['bmewburn.vscode-intelephense-client', 'esbenp.prettier-vscode']],
            ),
            new TabOutput(
                type: TabType::MCP_SERVERS,
                content: ['mcp_servers' => [
                    ['name' => 'filesystem', 'command' => 'npx', 'args' => ['-y', '@modelcontextprotocol/server-filesystem']],
                ]],
            ),
        ];
    }

    #[Test]
    public function get_agent_md_content_returns_null_when_no_ai_context_tab(): void
    {
        $resolved = new ResolvedTabs([]);

        $this->assertNull($resolved->getAgentMdContent());
    }

    #[Test]
    public function get_agent_md_content_returns_content_when_ai_context_tab_exists(): void
    {
        $resolved = new ResolvedTabs($this->makeTabs());

        $content = $resolved->getAgentMdContent();

        $this->assertNotNull($content);
        $this->assertStringContainsString('Agent Context', $content);
    }

    #[Test]
    public function get_vscode_extensions_returns_empty_array_when_no_vscode_tab(): void
    {
        $resolved = new ResolvedTabs([]);

        $this->assertSame([], $resolved->getVscodeExtensions());
    }

    #[Test]
    public function get_vscode_extensions_returns_extensions_when_tab_exists(): void
    {
        $resolved = new ResolvedTabs($this->makeTabs());

        $extensions = $resolved->getVscodeExtensions();

        $this->assertCount(2, $extensions);
        $this->assertContains('bmewburn.vscode-intelephense-client', $extensions);
        $this->assertContains('esbenp.prettier-vscode', $extensions);
    }

    #[Test]
    public function get_vscode_install_command_returns_empty_string_when_no_extensions(): void
    {
        $resolved = new ResolvedTabs([]);

        $this->assertSame('', $resolved->getVscodeInstallCommand());
    }

    #[Test]
    public function get_vscode_install_command_returns_command_when_extensions_exist(): void
    {
        $resolved = new ResolvedTabs($this->makeTabs());

        $command = $resolved->getVscodeInstallCommand();

        $this->assertStringContainsString('code --install-extension', $command);
        $this->assertStringContainsString('bmewburn.vscode-intelephense-client', $command);
        $this->assertStringContainsString('esbenp.prettier-vscode', $command);
    }

    #[Test]
    public function get_mcp_servers_returns_empty_array_when_no_mcp_tab(): void
    {
        $resolved = new ResolvedTabs([]);

        $this->assertSame([], $resolved->getMcpServers());
    }

    #[Test]
    public function get_mcp_servers_returns_config_when_tab_exists(): void
    {
        $resolved = new ResolvedTabs($this->makeTabs());

        $servers = $resolved->getMcpServers();

        $this->assertArrayHasKey('mcp_servers', $servers);
        $this->assertCount(1, $servers['mcp_servers']);
        $this->assertSame('filesystem', $servers['mcp_servers'][0]['name']);
    }
}
