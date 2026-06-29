<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\DeleteBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Actions\NotifySubscribers;
use App\Modules\Marketplace\Models\Subscription;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use App\Modules\Shared\ValueObjects\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DeleteBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;
    private DeleteBlueprint $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Delete Test',
            'email' => 'delete@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $this->organization = $createOrg->execute($this->user, 'Delete Org', 'delete-org');

        $this->action = new DeleteBlueprint();
    }

    public function test_deleting_public_blueprint_dispatches_notify_subscribers(): void
    {
        Bus::fake();

        $blueprint = $this->createPublicBlueprint();
        $blueprint->subscribers_count = 3;
        $blueprint->save();

        $this->action->execute($blueprint);

        Bus::assertDispatched(NotifySubscribers::class, function ($job) use ($blueprint) {
            return $job->blueprintId === $blueprint->id
                && $job->type === 'blueprint_deleted';
        });
    }

    public function test_deleting_public_blueprint_with_no_subscribers_skips_job(): void
    {
        Bus::fake();

        $blueprint = $this->createPublicBlueprint();

        $this->action->execute($blueprint);

        Bus::assertNotDispatched(NotifySubscribers::class);
    }

    public function test_deleting_public_blueprint_unlinks_subscribers(): void
    {
        $blueprint = $this->createPublicBlueprint();
        $blueprint->subscribers_count = 2;
        $blueprint->save();

        // Create subscribers
        $subscriber = $this->createSubscriber();
        $copy = $this->createCopyBlueprint($subscriber['org']);

        Subscription::create([
            'user_id' => $subscriber['user']->id,
            'subscribed_blueprint_id' => $blueprint->id,
            'copied_blueprint_id' => $copy->id,
            'notify_on_update' => true,
        ]);

        $this->action->execute($blueprint);

        // Assert subscriptions were unlinked (not deleted — audit trail preserved)
        $this->assertEquals(1, Subscription::count());
        $this->assertNull(Subscription::first()->subscribed_blueprint_id);
    }

    public function test_subscriber_copies_remain_after_blueprint_deletion(): void
    {
        $blueprint = $this->createPublicBlueprint();
        $blueprint->subscribers_count = 1;
        $blueprint->save();

        $subscriber = $this->createSubscriber();
        $copy = $this->createCopyBlueprint($subscriber['org']);

        Subscription::create([
            'user_id' => $subscriber['user']->id,
            'subscribed_blueprint_id' => $blueprint->id,
            'copied_blueprint_id' => $copy->id,
            'notify_on_update' => true,
        ]);

        $this->action->execute($blueprint);

        // The copy should still exist and not have been deleted
        $copy->refresh();
        $this->assertNotNull($copy);
        $this->assertEquals($subscriber['org']->id, $copy->organization_id);
    }

    public function test_deleting_private_blueprint_skips_notification_job(): void
    {
        Bus::fake();

        $blueprint = $this->createBlueprint([
            'is_public' => false,
        ]);

        $this->action->execute($blueprint);

        Bus::assertNotDispatched(NotifySubscribers::class);
    }

    public function test_deleting_private_blueprint_does_not_unlink_subscriptions(): void
    {
        $blueprint = $this->createBlueprint([
            'is_public' => false,
        ]);

        // Create a subscription linking to this blueprint
        $subscriber = $this->createSubscriber();
        $copy = $this->createCopyBlueprint($subscriber['org']);

        $sub = Subscription::create([
            'user_id' => $subscriber['user']->id,
            'subscribed_blueprint_id' => $blueprint->id,
            'copied_blueprint_id' => $copy->id,
            'notify_on_update' => true,
        ]);

        $this->action->execute($blueprint);

        // Private blueprint deletion does NOT remove subscriptions
        $this->assertEquals(1, Subscription::count());
    }

    public function test_it_soft_deletes_blueprint(): void
    {
        $blueprint = $this->createPublicBlueprint();

        $this->action->execute($blueprint);

        $this->assertSoftDeleted($blueprint);
    }

    private function createPublicBlueprint(): Blueprint
    {
        return $this->createBlueprint(['is_public' => true]);
    }

    private function createBlueprint(array $extra = []): Blueprint
    {
        return Blueprint::create(array_merge([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $this->organization->id,
            'slug' => 'delete-bp-' . uniqid(),
            'title' => 'Delete Test BP',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $this->user->id,
        ], $extra));
    }

    /**
     * @return array{user: User, org: Organization}
     */
    private function createSubscriber(): array
    {
        $plan = Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Subscriber',
            'email' => 'sub-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $org = $createOrg->execute($user, 'Sub Org ' . uniqid(), 'sub-org-' . uniqid());

        return ['user' => $user, 'org' => $org];
    }

    private function createCopyBlueprint(Organization $org): Blueprint
    {
        return Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $org->id,
            'slug' => 'copy-' . uniqid(),
            'title' => 'Copy of Blueprint',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $org->owner_id,
        ]);
    }
}
