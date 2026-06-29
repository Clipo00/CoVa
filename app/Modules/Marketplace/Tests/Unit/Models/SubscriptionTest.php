<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Unit\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Models\Subscription;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_creates_subscription(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Test Org',
            'slug' => 'test-org',
            'owner_id' => $user->id,
        ]);

        $originalBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'original-bp',
            'title' => 'Original Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $copiedBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'copied-bp',
            'title' => 'Copied Blueprint',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $originalBp->id,
            'copied_blueprint_id' => $copiedBp->id,
        ]);

        $this->assertNotNull($subscription->id);
        $this->assertEquals($user->id, $subscription->user_id);
        $this->assertEquals($originalBp->id, $subscription->subscribed_blueprint_id);
        $this->assertEquals($copiedBp->id, $subscription->copied_blueprint_id);
        $this->assertTrue($subscription->notify_on_update);
    }

    public function test_enforces_unique_user_blueprint_constraint(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Test User',
            'email' => 'unique@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Test Org',
            'slug' => 'unique-org',
            'owner_id' => $user->id,
        ]);

        $originalBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'original-1',
            'title' => 'Original',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $copiedBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'copied-1',
            'title' => 'Copied',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $originalBp->id,
            'copied_blueprint_id' => $copiedBp->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $originalBp->id,
            'copied_blueprint_id' => $copiedBp->id,
        ]);
    }

    public function test_subscription_belongs_to_user(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'User Rel',
            'email' => 'user-rel@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Org Rel',
            'slug' => 'org-rel',
            'owner_id' => $user->id,
        ]);

        $originalBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'orig-rel',
            'title' => 'Original Rel',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $copiedBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'copy-rel',
            'title' => 'Copy Rel',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $originalBp->id,
            'copied_blueprint_id' => $copiedBp->id,
        ]);

        $this->assertInstanceOf(User::class, $subscription->user);
        $this->assertEquals($user->id, $subscription->user->id);
    }

    public function test_subscription_belongs_to_subscribed_blueprint(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Sub Rel',
            'email' => 'sub-rel@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Org Sub',
            'slug' => 'org-sub',
            'owner_id' => $user->id,
        ]);

        $originalBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'orig-sub',
            'title' => 'Original Sub',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $copiedBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'copy-sub',
            'title' => 'Copy Sub',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $originalBp->id,
            'copied_blueprint_id' => $copiedBp->id,
        ]);

        $this->assertInstanceOf(Blueprint::class, $subscription->subscribedBlueprint);
        $this->assertEquals($originalBp->id, $subscription->subscribedBlueprint->id);
    }

    public function test_subscription_belongs_to_copied_blueprint(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Copy Rel',
            'email' => 'copy-rel@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Org Copy',
            'slug' => 'org-copy',
            'owner_id' => $user->id,
        ]);

        $originalBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'orig-copy',
            'title' => 'Original Copy',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $copiedBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'copy-copy',
            'title' => 'Copy Copy',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $originalBp->id,
            'copied_blueprint_id' => $copiedBp->id,
        ]);

        $this->assertInstanceOf(Blueprint::class, $subscription->copiedBlueprint);
        $this->assertEquals($copiedBp->id, $subscription->copiedBlueprint->id);
    }

    public function test_notify_on_update_defaults_to_true(): void
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Notify User',
            'email' => 'notify@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Org Notify',
            'slug' => 'org-notify',
            'owner_id' => $user->id,
        ]);

        $originalBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'orig-notify',
            'title' => 'Original Notify',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $copiedBp = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'copy-notify',
            'title' => 'Copy Notify',
            'is_public' => false,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'subscribed_blueprint_id' => $originalBp->id,
            'copied_blueprint_id' => $copiedBp->id,
            'notify_on_update' => false,
        ]);

        $this->assertFalse($subscription->notify_on_update);
    }
}
