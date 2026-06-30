<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\UpdateBlueprint;
use App\Modules\Blueprint\Exceptions\MaxVariablesReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Actions\NotifySubscribers;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use App\Modules\Shared\ValueObjects\Uuid;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class UpdateBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organization $organization;

    private UpdateBlueprint $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Update Test',
            'email' => 'update@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization;
        $this->organization = $createOrg->execute($this->user, 'Update Org', 'update-org');

        $this->action = new UpdateBlueprint;
    }

    public function test_it_updates_blueprint_title(): void
    {
        $blueprint = $this->createBlueprint(true);

        $updated = $this->action->execute($blueprint, [
            'title' => 'Updated Title',
        ]);

        $this->assertEquals('Updated Title', $updated->title);
    }

    public function test_updating_public_blueprint_dispatches_notify_subscribers(): void
    {
        Bus::fake();

        $blueprint = $this->createBlueprint(true);
        $blueprint->subscribers_count = 5;
        $blueprint->save();

        $this->action->execute($blueprint, [
            'title' => 'Updated Public BP',
        ]);

        Bus::assertDispatched(NotifySubscribers::class, function ($job) use ($blueprint) {
            return $job->blueprintId === $blueprint->id
                && $job->type === 'blueprint_updated';
        });
    }

    public function test_updating_public_blueprint_with_no_subscribers_skips_job(): void
    {
        Bus::fake();

        $blueprint = $this->createBlueprint(true);
        // subscribers_count defaults to 0

        $this->action->execute($blueprint, [
            'title' => 'Updated No Subs',
        ]);

        Bus::assertNotDispatched(NotifySubscribers::class);
    }

    public function test_updating_private_blueprint_skips_notification_job(): void
    {
        Bus::fake();

        $blueprint = $this->createBlueprint(false);

        $this->action->execute($blueprint, [
            'title' => 'Updated Private BP',
        ]);

        Bus::assertNotDispatched(NotifySubscribers::class);
    }

    public function test_it_enforces_variable_limit_including_segments_on_update(): void
    {
        $blueprint = $this->createBlueprint(false);

        // Free plan allows 50 items — 48 variables + 3 segments = 51 > 50
        $variables = [];
        for ($i = 1; $i <= 48; $i++) {
            $variables[] = ['key' => "VAR_{$i}", 'type' => 'fixed', 'default_value' => ''];
        }

        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'segments' => [
                    ['type' => 'preset', 'name' => 'psr12', 'content' => null],
                    ['type' => 'skill', 'name' => 'stripe', 'content' => null],
                    ['type' => 'custom', 'name' => 'My Rules', 'content' => 'Always use types.'],
                ],
            ]],
        ];

        $this->expectException(MaxVariablesReachedException::class);
        $this->action->execute($blueprint, [
            'title' => 'Over Limit Update',
            'tabs_config' => $tabsConfig,
        ], $variables);
    }

    public function test_it_passes_variable_limit_with_segments_within_budget_on_update(): void
    {
        $blueprint = $this->createBlueprint(false);

        // Free plan allows 50 items — 47 variables + 3 segments = 50 OK
        $variables = [];
        for ($i = 1; $i <= 47; $i++) {
            $variables[] = ['key' => "VAR_{$i}", 'type' => 'fixed', 'default_value' => ''];
        }

        $tabsConfig = [
            ['type' => 'ai_context', 'config' => [
                'segments' => [
                    ['type' => 'preset', 'name' => 'psr12', 'content' => null],
                    ['type' => 'skill', 'name' => 'stripe', 'content' => null],
                    ['type' => 'custom', 'name' => 'My Rules', 'content' => 'Always use types.'],
                ],
            ]],
        ];

        $updated = $this->action->execute($blueprint, [
            'title' => 'Within Limit Update',
            'tabs_config' => $tabsConfig,
        ], $variables);

        $this->assertEquals('Within Limit Update', $updated->title);
    }

    private function createBlueprint(bool $isPublic): Blueprint
    {
        return Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $this->organization->id,
            'slug' => 'update-bp-'.uniqid(),
            'title' => 'Original Title',
            'is_public' => $isPublic,
            'tabs_config' => [],
            'created_by' => $this->user->id,
        ]);
    }
}
