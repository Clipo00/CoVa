<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Actions\VoteOnBlueprint;
use App\Modules\Marketplace\Models\Vote;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteOnBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Blueprint $blueprint;
    private VoteOnBlueprint $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $plan = Plan::where('slug', 'free')->first();
        $this->user = User::create([
            'name' => 'Vote Action Test',
            'email' => 'vote-action-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Vote Action Org',
            'slug' => 'vote-action-org-' . uniqid(),
            'owner_id' => $this->user->id,
        ]);

        $this->blueprint = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => 'vote-action-bp-' . uniqid(),
            'title' => 'Vote Action Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $this->user->id,
            'votes_count' => 0,
        ]);

        $this->action = new VoteOnBlueprint();
    }

    public function test_upvote_creates_vote_record_and_increments_count(): void
    {
        $result = $this->action->execute($this->user, $this->blueprint, 1);

        $this->assertEquals(1, $result['votes_count']);
        $this->assertEquals(1, $result['user_vote']);

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $this->user->id,
            'blueprint_id' => $this->blueprint->id,
            'vote' => 1,
        ]);
    }

    public function test_downvote_creates_vote_record_and_decrements_count(): void
    {
        $result = $this->action->execute($this->user, $this->blueprint, -1);

        $this->assertEquals(-1, $result['votes_count']);
        $this->assertEquals(-1, $result['user_vote']);

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $this->user->id,
            'blueprint_id' => $this->blueprint->id,
            'vote' => -1,
        ]);
    }

    public function test_upvote_again_toggles_off_and_decrements_count(): void
    {
        $this->action->execute($this->user, $this->blueprint, 1);
        $this->assertEquals(1, $this->blueprint->fresh()->votes_count);

        $result = $this->action->execute($this->user, $this->blueprint, 1);

        $this->assertEquals(0, $result['votes_count']);
        $this->assertNull($result['user_vote']);

        $this->assertDatabaseMissing('blueprint_votes', [
            'user_id' => $this->user->id,
            'blueprint_id' => $this->blueprint->id,
        ]);
    }

    public function test_downvote_again_toggles_off_and_increments_count(): void
    {
        $this->action->execute($this->user, $this->blueprint, -1);
        $this->assertEquals(-1, $this->blueprint->fresh()->votes_count);

        $result = $this->action->execute($this->user, $this->blueprint, -1);

        $this->assertEquals(0, $result['votes_count']);
        $this->assertNull($result['user_vote']);

        $this->assertDatabaseMissing('blueprint_votes', [
            'user_id' => $this->user->id,
            'blueprint_id' => $this->blueprint->id,
        ]);
    }

    public function test_flips_from_upvote_to_downvote(): void
    {
        $this->blueprint->votes_count = 5;
        $this->blueprint->save();

        Vote::create([
            'user_id' => $this->user->id,
            'blueprint_id' => $this->blueprint->id,
            'vote' => 1,
        ]);

        $result = $this->action->execute($this->user, $this->blueprint, -1);

        $this->assertEquals(3, $result['votes_count']); // 5 - 2 = 3
        $this->assertEquals(-1, $result['user_vote']);

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $this->user->id,
            'blueprint_id' => $this->blueprint->id,
            'vote' => -1,
        ]);
    }

    public function test_flips_from_downvote_to_upvote(): void
    {
        $this->blueprint->votes_count = 5;
        $this->blueprint->save();

        Vote::create([
            'user_id' => $this->user->id,
            'blueprint_id' => $this->blueprint->id,
            'vote' => -1,
        ]);

        $result = $this->action->execute($this->user, $this->blueprint, 1);

        $this->assertEquals(7, $result['votes_count']); // 5 + 2 = 7
        $this->assertEquals(1, $result['user_vote']);

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $this->user->id,
            'blueprint_id' => $this->blueprint->id,
            'vote' => 1,
        ]);
    }
}
