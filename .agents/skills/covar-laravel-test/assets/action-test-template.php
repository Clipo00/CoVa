<?php

declare(strict_types=1);

namespace App\Modules\{Module}\Tests\Unit\Actions;

use App\Modules\{Module}\Actions\{ActionName};
use App\Modules\{Module}\Exceptions\{Exception};
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {ActionName}Test extends TestCase
{
    use RefreshDatabase;

    private {ActionName} $action;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new {ActionName}();
        $this->organization = Organization::factory()->create();
    }

    public function test_action_creates_{model}_successfully(): void
    {
        ${model} = $this->action->execute(
            organization: $this->organization,
            title: 'Test {Model}',
            slug: 'test-{model}',
        );

        $this->assertNotNull(${model}->id);
        $this->assertEquals('Test {Model}', ${model}->title);
    }

    public function test_action_throws_exception_when_limit_reached(): void
    {
        // Setup: crear tantos como permita el plan
        $plan = $this->organization->plan;

        $this->expectException({Exception}::class);
        
        $this->action->execute(
            organization: $this->organization,
            title: 'One More',
            slug: 'one-more',
        );
    }
}