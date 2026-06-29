<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use App\Modules\Marketplace\Actions\SubscribeToBlueprint;
use App\Modules\Marketplace\Models\Subscription;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscribeToBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;
    private SubscribeToBlueprint $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Subscribe Test',
            'email' => 'subscribe-test@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $createOrg = new CreateOrganization();
        $this->organization = $createOrg->execute($this->user, 'Subscriber Org', 'subscriber-org');

        $this->action = new SubscribeToBlueprint();
    }

    // 2.2.1: Subscribe creates a blueprint copy
    public function test_subscribe_creates_blueprint_copy(): void
    {
        $blueprint = $this->createPublicBlueprintWithVariables();

        $copy = $this->action->execute($this->user, $blueprint);

        // Assert: new blueprint created with new UUID
        $this->assertNotNull($copy->id);
        $this->assertNotNull($copy->uuid);
        $this->assertNotEquals($blueprint->uuid, $copy->uuid);

        // Assert: copy belongs to user's org
        $this->assertEquals($this->organization->id, $copy->organization_id);

        // Assert: copy has is_public = false
        $this->assertFalse($copy->is_public);

        // Assert: copy has same title
        $this->assertEquals($blueprint->title, $copy->title);
    }

    // 2.2.1b: Subscribe copies tabs_config
    public function test_subscribe_copies_tabs_config(): void
    {
        $blueprint = $this->createPublicBlueprintWithTabs();

        $copy = $this->action->execute($this->user, $blueprint);

        $this->assertCount(1, $copy->tabs_config);
        $this->assertEquals('ai_context', $copy->tabs_config[0]['type']);
    }

    // 2.2.1c: Subscribe copies variables
    public function test_subscribe_copies_variables(): void
    {
        $blueprint = $this->createPublicBlueprintWithVariables();

        $copy = $this->action->execute($this->user, $blueprint);

        $this->assertCount(2, $copy->variables);
        $this->assertEquals('DB_HOST', $copy->variables[0]->key);
        $this->assertEquals('localhost', $copy->variables[0]->default_value);
        $this->assertEquals('APP_KEY', $copy->variables[1]->key);
        $this->assertTrue($copy->variables[1]->is_secret);
    }

    // 2.2.1d: Subscribe creates Subscription record
    public function test_subscribe_creates_subscription_record(): void
    {
        $blueprint = $this->createPublicBlueprintWithVariables();

        $copy = $this->action->execute($this->user, $blueprint);

        $subscription = Subscription::where('user_id', $this->user->id)
            ->where('subscribed_blueprint_id', $blueprint->id)
            ->first();

        $this->assertNotNull($subscription);
        $this->assertEquals($copy->id, $subscription->copied_blueprint_id);
        $this->assertTrue($subscription->notify_on_update);
    }

    // 2.2.1e: Subscribe increments subscribers_count on original
    public function test_subscribe_increments_subscribers_count(): void
    {
        $blueprint = $this->createPublicBlueprintWithVariables();

        $this->assertEquals(0, $blueprint->subscribers_count);

        $this->action->execute($this->user, $blueprint);

        $blueprint->refresh();
        $this->assertEquals(1, $blueprint->subscribers_count);
    }

    // 2.2.2: Cannot subscribe twice
    public function test_cannot_subscribe_twice(): void
    {
        $blueprint = $this->createPublicBlueprintWithVariables();

        $this->action->execute($this->user, $blueprint);

        $this->expectException(\RuntimeException::class);

        $this->action->execute($this->user, $blueprint);
    }

    // 2.2.3: Subscription respects plan blueprint limit
    public function test_subscription_respects_plan_blueprint_limit(): void
    {
        $blueprint = $this->createPublicBlueprintWithVariables();

        // Fill the Free plan limit (3) by creating blueprints directly (bypass plan check)
        foreach (['BP 1', 'BP 2', 'BP 3'] as $title) {
            Blueprint::create([
                'uuid' => (string) \App\Modules\Shared\ValueObjects\Uuid::generate(),
                'organization_id' => $this->organization->id,
                'slug' => 'fill-bp-' . uniqid(),
                'title' => $title,
                'is_public' => false,
                'tabs_config' => [],
                'created_by' => $this->user->id,
            ]);
        }

        // Subscribe should now throw because plan limit is reached
        $this->expectException(\App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException::class);

        $this->action->execute($this->user, $blueprint);
    }

    private function createPublicBlueprintWithTabs(): Blueprint
    {
        $this->actingAs($this->user);
        $action = new CreateBlueprint();
        $blueprint = $action->execute(
            organization: $this->organization,
            title: 'Blueprint with Tabs',
            slug: 'bp-tabs-' . uniqid(),
            tabsConfig: [
                ['type' => 'ai_context', 'config' => [
                    'presets' => ['laravel-conventions'],
                    'skills' => [],
                    'custom_rules' => 'Test rule.',
                ]],
            ],
        );
        $blueprint->is_public = true;
        $blueprint->save();
        return $blueprint;
    }

    private function createPublicBlueprintWithVariables(): Blueprint
    {
        $this->actingAs($this->user);
        $action = new CreateBlueprint();
        $blueprint = $action->execute(
            organization: $this->organization,
            title: 'Blueprint with Vars',
            slug: 'bp-vars-' . uniqid(),
            tabsConfig: [],
            variables: [
                ['key' => 'DB_HOST', 'type' => 'fixed', 'default_value' => 'localhost'],
                ['key' => 'APP_KEY', 'type' => 'empty', 'default_value' => '', 'is_secret' => true],
            ],
        );
        $blueprint->is_public = true;
        $blueprint->save();
        return $blueprint;
    }
}
