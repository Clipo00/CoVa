<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Tabs;

use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Tabs\McpServersTab;
use PHPUnit\Framework\TestCase;

class McpServersTabTest extends TestCase
{
    private McpServersTab $tab;

    protected function setUp(): void
    {
        $this->tab = new McpServersTab;
    }

    public function test_type_returns_mcp_servers(): void
    {
        $this->assertEquals('mcp_servers', $this->tab->type());
    }

    public function test_generate_returns_servers_config(): void
    {
        $config = [
            'servers' => [
                [
                    'name' => 'filesystem',
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-filesystem', '/workspace'],
                ],
            ],
        ];

        $output = $this->tab->generate($config);

        $this->assertInstanceOf(TabOutput::class, $output);
        $this->assertEquals(TabType::MCP_SERVERS, $output->type);
        $this->assertIsArray($output->content);
        $this->assertArrayHasKey('mcp_servers', $output->content);
    }

    public function test_generate_handles_multiple_servers(): void
    {
        $config = [
            'servers' => [
                [
                    'name' => 'filesystem',
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-filesystem'],
                ],
                [
                    'name' => 'github',
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-github'],
                ],
            ],
        ];

        $output = $this->tab->generate($config);

        $this->assertCount(2, $output->content['mcp_servers']);
    }

    public function test_generate_handles_empty_servers(): void
    {
        $config = [
            'servers' => [],
        ];

        $output = $this->tab->generate($config);

        $this->assertEquals([], $output->content['mcp_servers']);
    }
}
