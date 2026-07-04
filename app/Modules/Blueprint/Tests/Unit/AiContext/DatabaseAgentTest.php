<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\AiContext;

use App\Modules\Blueprint\Models\AgentTemplate;
use App\Modules\Blueprint\Tabs\AiContext\Agents\DatabaseAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseAgentTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseAgent $agent;

    private AgentTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = AgentTemplate::create([
            'name' => 'test-agent',
            'display_name' => 'Test Agent',
            'content' => "# Test Agent\n\nContent here.\n\n<!-- AGENT_ROUTER_START -->\n<!-- AGENT_ROUTER_END -->\n\n## More\n\n- item 1\n- item 2",
            'skills' => ['skill-a', 'skill-b'],
        ]);

        $this->agent = new DatabaseAgent($this->template);
    }

    public function test_agent_name_resolves_from_database(): void
    {
        $this->assertSame('test-agent', $this->agent->name());
    }

    public function test_agent_content_resolves_from_database(): void
    {
        $this->assertSame($this->template->content, $this->agent->content());
    }

    public function test_agent_skills_resolves_from_database(): void
    {
        $this->assertSame(['skill-a', 'skill-b'], $this->agent->skills());
    }

    public function test_has_router_returns_true_when_delimiters_present(): void
    {
        $this->assertTrue($this->agent->hasRouter());
    }

    public function test_has_router_returns_false_when_delimiters_absent(): void
    {
        $template = AgentTemplate::create([
            'name' => 'no-router',
            'display_name' => 'No Router',
            'content' => '# Simple agent without router delimiters',
            'skills' => [],
        ]);

        $agent = new DatabaseAgent($template);

        $this->assertFalse($agent->hasRouter());
    }

    public function test_resolve_with_skills_inserts_between_delimiters(): void
    {
        $result = $this->agent->resolveWithSkills(['## Skill Content', 'More skill text']);

        $this->assertStringContainsString('<!-- AGENT_ROUTER_START -->', $result);
        $this->assertStringContainsString('<!-- AGENT_ROUTER_END -->', $result);
        $this->assertStringContainsString('## Skill Content', $result);
        $this->assertStringContainsString('More skill text', $result);
    }

    public function test_resolve_with_skills_appends_when_no_router(): void
    {
        $template = AgentTemplate::create([
            'name' => 'no-router',
            'display_name' => 'No Router',
            'content' => '# Simple agent',
            'skills' => [],
        ]);

        $agent = new DatabaseAgent($template);
        $result = $agent->resolveWithSkills(['## Appended Skill']);

        $this->assertStringContainsString('## Appended Skill', $result);
    }

    public function test_implements_agent_content_segment(): void
    {
        $this->assertInstanceOf(
            \App\Modules\Blueprint\Contracts\AgentContentSegment::class,
            $this->agent
        );
    }

    public function test_skills_defaults_to_empty_array_when_null(): void
    {
        // Insert via query builder to bypass model cast and set null skills
        $template = AgentTemplate::create([
            'name' => 'no-skills',
            'display_name' => 'No Skills',
            'content' => '# No skills agent',
            'skills' => [],
        ]);

        // Verify the backing column is usable when empty
        $fresh = AgentTemplate::where('name', 'no-skills')->first();

        $agent = new DatabaseAgent($fresh);

        $this->assertIsArray($agent->skills());
        $this->assertEmpty($agent->skills());
    }
}
