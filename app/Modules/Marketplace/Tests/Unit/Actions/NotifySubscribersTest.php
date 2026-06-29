<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Actions\NotifySubscribers;
use App\Modules\Marketplace\Models\Notification;
use App\Modules\Marketplace\Models\Subscription;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use App\Modules\Shared\ValueObjects\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotifySubscribersTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Organization $organization;
    private Blueprint $blueprint;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();

        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $this->organization = $createOrg->execute($this->owner, 'Test Org', 'test-org');

        $this->blueprint = Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $this->organization->id,
            'slug' => 'test-bp',
            'title' => 'Test Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $this->owner->id,
        ]);
    }

    public function test_it_creates_notifications_for_all_subscribers(): void
    {
        // Create 3 subscribers
        $subscribers = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = User::create([
                'name' => "Subscriber {$i}",
                'email' => "sub{$i}@example.com",
                'password' => bcrypt('password'),
                'plan_id' => Plan::where('slug', 'free')->first()->id,
            ]);

            $org = $this->createSubscriberOrg($user, $i);
            $copy = $this->createCopyBlueprint($org, $i);

            $subscribers[] = Subscription::create([
                'user_id' => $user->id,
                'subscribed_blueprint_id' => $this->blueprint->id,
                'copied_blueprint_id' => $copy->id,
                'notify_on_update' => true,
            ]);
        }

        // Execute the job
        $job = new NotifySubscribers($this->blueprint->id, 'blueprint_updated');
        $job->handle();

        // Assert: 3 notifications created, one per subscriber
        $this->assertEquals(3, Notification::count());

        foreach ($subscribers as $sub) {
            $notif = Notification::where('user_id', $sub->user_id)->first();
            $this->assertNotNull($notif);
            $this->assertEquals('blueprint_updated', $notif->type);
            $this->assertEquals($this->blueprint->uuid, $notif->data['blueprint_uuid']);
            $this->assertEquals($this->blueprint->title, $notif->data['blueprint_title']);
        }
    }

    public function test_it_does_not_notify_subscribers_with_notify_on_update_false(): void
    {
        $user = User::create([
            'name' => 'Silent Sub',
            'email' => 'silent@example.com',
            'password' => bcrypt('password'),
            'plan_id' => Plan::where('slug', 'free')->first()->id,
        ]);

        $org = $this->createSubscriberOrg($user, 1);
        $copy = $this->createCopyBlueprint($org, 1);

        Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $this->blueprint->id,
            'copied_blueprint_id' => $copy->id,
            'notify_on_update' => false,
        ]);

        $job = new NotifySubscribers($this->blueprint->id, 'blueprint_deleted');
        $job->handle();

        $this->assertEquals(0, Notification::count());
    }

    public function test_it_handles_soft_deleted_blueprint(): void
    {
        $user = User::create([
            'name' => 'Sub After Delete',
            'email' => 'subdel@example.com',
            'password' => bcrypt('password'),
            'plan_id' => Plan::where('slug', 'free')->first()->id,
        ]);

        $org = $this->createSubscriberOrg($user, 1);
        $copy = $this->createCopyBlueprint($org, 1);

        Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $this->blueprint->id,
            'copied_blueprint_id' => $copy->id,
            'notify_on_update' => true,
        ]);

        // Soft delete the blueprint
        $this->blueprint->delete();

        // Job should still work because it queries with trashed
        $job = new NotifySubscribers($this->blueprint->id, 'blueprint_deleted');
        $job->handle();

        $this->assertEquals(1, Notification::count());
        $notification = Notification::first();
        $this->assertEquals('blueprint_deleted', $notification->type);
        $this->assertEquals($this->blueprint->title, $notification->data['blueprint_title']);
    }

    private function createSubscriberOrg(User $user, int $suffix): Organization
    {
        $createOrg = new CreateOrganization();
        return $createOrg->execute($user, "Sub Org {$suffix}", "sub-org-{$suffix}");
    }

    private function createCopyBlueprint(Organization $org, int $suffix): Blueprint
    {
        return Blueprint::create([
            'uuid' => (string) Uuid::generate(),
            'organization_id' => $org->id,
            'slug' => "copy-bp-{$suffix}",
            'title' => "Copy {$suffix}",
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $org->owner_id,
        ]);
    }
}
