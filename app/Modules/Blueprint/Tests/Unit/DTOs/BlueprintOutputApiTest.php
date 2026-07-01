<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\DTOs;

use App\Modules\Blueprint\DTOs\BlueprintOutput;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlueprintOutputApiTest extends TestCase
{
    use RefreshDatabase;

    private Blueprint $blueprint;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $org = Organization::create([
            'slug' => 'test-org',
            'name' => 'Test Org',
            'owner_id' => $this->createUserWithPlan($plan)->id,
        ]);

        $this->organization = $org;

        $this->blueprint = Blueprint::create([
            'uuid' => '550e8400-e29b-41d4-a716-446655440000',
            'organization_id' => $org->id,
            'slug' => 'test-bp',
            'title' => 'Test Blueprint',
            'description' => 'A blueprint for API testing',
            'tabs_config' => [],
            'created_by' => $org->owner_id,
        ]);
    }

    private function createUserWithPlan(Plan $plan): \App\Modules\Auth\Models\User
    {
        return \App\Modules\Auth\Models\User::create([
            'name' => 'Test User',
            'email' => fake()->email(),
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);
    }

    public function test_to_api_array_masks_secret_variables(): void
    {
        $this->blueprint->variables()->createMany([
            [
                'key' => 'DB_HOST',
                'type' => 'fixed',
                'default_value' => 'localhost',
                'is_secret' => false,
                'section' => 'database',
                'sort_order' => 0,
            ],
            [
                'key' => 'DB_PASSWORD',
                'type' => 'fixed',
                'default_value' => 'supersecret',
                'is_secret' => true,
                'section' => 'database',
                'sort_order' => 1,
            ],
            [
                'key' => 'APP_KEY',
                'type' => 'fixed',
                'default_value' => 'base64:abc123',
                'is_secret' => false,
                'section' => 'app',
                'sort_order' => 2,
            ],
        ]);

        $output = new BlueprintOutput($this->blueprint, []);
        $result = $output->toApiArray();

        // Blueprint metadata
        $this->assertEquals('test-bp', $result['slug']);
        $this->assertEquals('Test Blueprint', $result['title']);
        $this->assertEquals('A blueprint for API testing', $result['description']);

        // Variables count
        $this->assertCount(3, $result['variables']);

        // Non-secret variable shows real value
        $dbHost = collect($result['variables'])->firstWhere('key', 'DB_HOST');
        $this->assertEquals('localhost', $dbHost['default_value']);
        $this->assertFalse($dbHost['is_secret']);
        $this->assertEquals('fixed', $dbHost['type']);
        $this->assertEquals('database', $dbHost['section']);

        // Secret variable has empty string
        $dbPass = collect($result['variables'])->firstWhere('key', 'DB_PASSWORD');
        $this->assertEquals('', $dbPass['default_value']);
        $this->assertTrue($dbPass['is_secret']);

        // Non-secret APP_KEY shows its value
        $appKey = collect($result['variables'])->firstWhere('key', 'APP_KEY');
        $this->assertEquals('base64:abc123', $appKey['default_value']);
        $this->assertFalse($appKey['is_secret']);
    }

    public function test_to_api_array_includes_tab_resolved_content(): void
    {
        $tabs = [
            new TabOutput(TabType::AI_CONTEXT, "# Agent instructions\n\nBe concise.", 'agent.md'),
            new TabOutput(TabType::VSCODE_EXTENSIONS, [
                'extensions' => ['bradlc.vscode-tailwindcss', 'dbaeumer.vscode-eslint'],
            ]),
            new TabOutput(TabType::MCP_SERVERS, [
                'filesystem' => [
                    'command' => 'npx',
                    'args' => ['-y', '@modelcontextprotocol/server-filesystem'],
                ],
            ]),
            new TabOutput(TabType::SCRIPTS, [
                'scripts' => [
                    ['command' => 'npm run dev', 'description' => 'Start dev server', 'order' => 1],
                ],
                'shell_script' => "#!/bin/bash\nnpm run dev\n",
            ]),
        ];

        $output = new BlueprintOutput($this->blueprint, $tabs);
        $result = $output->toApiArray();

        // Tab-resolved content
        $this->assertNotNull($result['agent_md']);
        $this->assertStringContainsString('Be concise', $result['agent_md']);

        $this->assertCount(2, $result['vscode_extensions']);
        $this->assertContains('bradlc.vscode-tailwindcss', $result['vscode_extensions']);

        $this->assertArrayHasKey('filesystem', $result['mcp_servers']);
        $this->assertEquals('npx', $result['mcp_servers']['filesystem']['command']);

        $this->assertCount(1, $result['scripts']);
        $this->assertEquals('npm run dev', $result['scripts'][0]['command']);

        $this->assertStringContainsString('npm run dev', $result['scripts_shell']);
    }

    public function test_to_api_array_includes_blueprint_metadata(): void
    {
        $output = new BlueprintOutput($this->blueprint, []);
        $result = $output->toApiArray();

        $this->assertArrayHasKey('uuid', $result);
        $this->assertArrayHasKey('slug', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('variables', $result);
        $this->assertArrayHasKey('agent_md', $result);
        $this->assertArrayHasKey('vscode_extensions', $result);
        $this->assertArrayHasKey('vscode_install_command', $result);
        $this->assertArrayHasKey('mcp_servers', $result);
        $this->assertArrayHasKey('scripts', $result);
        $this->assertArrayHasKey('scripts_shell', $result);
    }
}
