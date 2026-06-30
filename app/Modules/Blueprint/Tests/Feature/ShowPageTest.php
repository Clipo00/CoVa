<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowPageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Show Page Test',
            'email' => 'showpage@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $this->organization = $createOrg->execute($this->user, 'Show Org', 'show-org');

        $this->actingAs($this->user);
    }

    public function test_show_page_displays_agent_md_section(): void
    {
        $blueprint = $this->createBlueprintWithTabs([
            ['type' => 'ai_context', 'config' => [
                'presets' => ['laravel-conventions'],
                'skills' => [],
                'custom_rules' => 'Custom rule for testing.',
            ]],
        ]);

        $response = $this->get(route('blueprints.show', $blueprint->slug));

        $response->assertStatus(200);
        $response->assertSee('Agent Context');
        $response->assertSee('agent.md');
        $response->assertSee('Custom rule for testing.');
    }

    public function test_show_page_displays_vscode_extensions_section(): void
    {
        $blueprint = $this->createBlueprintWithTabs([
            ['type' => 'vscode_extensions', 'config' => [
                'extensions' => ['bmewburn.vscode-intelephense-client', 'esbenp.prettier-vscode'],
            ]],
        ]);

        $response = $this->get(route('blueprints.show', $blueprint->slug));

        $response->assertStatus(200);
        $response->assertSee('VSCode Extensions');
        $response->assertSee('bmewburn.vscode-intelephense-client');
        $response->assertSee('esbenp.prettier-vscode');
        $response->assertSee('code --install-extension');
    }

    public function test_show_page_displays_mcp_servers_section(): void
    {
        $blueprint = $this->createBlueprintWithTabs([
            ['type' => 'mcp_servers', 'config' => [
                'servers' => [
                    ['name' => 'filesystem', 'command' => 'npx', 'args' => ['-y', '@modelcontextprotocol/server-filesystem']],
                ],
            ]],
        ]);

        $response = $this->get(route('blueprints.show', $blueprint->slug));

        $response->assertStatus(200);
        $response->assertSee('MCP Servers');
        $response->assertSee('filesystem');
        $response->assertSee('npx');
    }

    public function test_show_page_displays_variables_section(): void
    {
        $blueprint = $this->createBlueprintWithVariables([
            ['key' => 'DB_HOST', 'type' => 'fixed', 'default_value' => 'localhost'],
            ['key' => 'APP_KEY', 'type' => 'empty', 'default_value' => '', 'is_secret' => true],
        ]);

        $response = $this->get(route('blueprints.show', $blueprint->slug));

        $response->assertStatus(200);
        $response->assertSee('DB_HOST');
        $response->assertSee('localhost');
        $response->assertSee('APP_KEY');
    }

    public function test_show_page_shows_empty_state_when_no_tabs(): void
    {
        $blueprint = $this->createBlueprintWithTabs([]);

        $response = $this->get(route('blueprints.show', $blueprint->slug));

        $response->assertStatus(200);
        // Should not show Agent Context, VSCode, or MCP sections
        $response->assertDontSee('Agent Context');
        // Should still show variables section
        $response->assertSee(route('blueprints.edit', $blueprint->slug));
    }

    private function createBlueprintWithTabs(array $tabsConfig): Blueprint
    {
        $action = new CreateBlueprint;

        return $action->execute(
            organization: $this->organization,
            title: 'Show Page Test BP',
            slug: 'show-page-test-'.uniqid(),
            tabsConfig: $tabsConfig,
        );
    }

    private function createBlueprintWithVariables(array $variables): Blueprint
    {
        $action = new CreateBlueprint;

        return $action->execute(
            organization: $this->organization,
            title: 'Show Page Vars BP',
            slug: 'show-page-vars-'.uniqid(),
            tabsConfig: [],
            variables: $variables,
        );
    }
}
