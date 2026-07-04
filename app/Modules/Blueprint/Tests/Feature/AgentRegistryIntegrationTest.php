<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Feature;

use App\Modules\Blueprint\Models\AgentTemplate;
use App\Modules\Blueprint\Tabs\AiContext\Agents\AgentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Agents\DatabaseAgent;
use Database\Seeders\AgentTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgentRegistryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AgentTemplateSeeder::class);
    }

    public function test_registry_contains_three_database_agents(): void
    {
        $registry = app('blueprint.agents');

        $this->assertInstanceOf(AgentRegistry::class, $registry);
        $this->assertCount(3, $registry->names());
        $this->assertContains('laravel-developer', $registry->names());
        $this->assertContains('frontend-developer', $registry->names());
        $this->assertContains('fullstack-developer', $registry->names());
    }

    public function test_registry_returns_database_agent_instances(): void
    {
        $registry = app('blueprint.agents');

        foreach ($registry->all() as $agent) {
            $this->assertInstanceOf(DatabaseAgent::class, $agent);
        }
    }

    public function test_registry_agent_content_resolves_from_database(): void
    {
        $registry = app('blueprint.agents');
        $agent = $registry->get('laravel-developer');

        $this->assertStringContainsString('Desarrollador Laravel', $agent->content());
        $this->assertTrue($agent->hasRouter());
    }

    public function test_registry_can_get_agent_by_name(): void
    {
        $registry = app('blueprint.agents');
        $agent = $registry->get('laravel-developer');

        $this->assertSame('laravel-developer', $agent->name());
    }

    public function test_registry_throws_for_unknown_agent(): void
    {
        $registry = app('blueprint.agents');

        $this->expectException(\App\Modules\Blueprint\Exceptions\UnknownSegmentException::class);

        $registry->get('non-existent-agent');
    }

    public function test_new_agent_row_appears_without_code_change(): void
    {
        // Add a new agent directly to the DB
        $template = AgentTemplate::create([
            'name' => 'new-dynamic-agent',
            'display_name' => 'Dynamic Agent',
            'content' => '# Dynamic agent added without code change',
            'skills' => [],
        ]);

        // Re-resolve the registry from the container (the singleton closure
        // will re-query AgentTemplate::all() on first call in this test)
        $registry = app('blueprint.agents');

        $this->assertTrue($registry->has('new-dynamic-agent'));
        $this->assertInstanceOf(DatabaseAgent::class, $registry->get('new-dynamic-agent'));
    }
}
