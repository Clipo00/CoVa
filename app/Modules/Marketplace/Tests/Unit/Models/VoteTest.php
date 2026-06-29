<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Tests\Unit\Models;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Models\Vote;
use App\Modules\Organization\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    private function createUserAndBlueprint(): array
    {
        $plan = \App\Modules\Shared\Models\Plan::where('slug', 'free')->first();
        $user = User::create([
            'name' => 'Vote User',
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'plan_id' => $plan->id,
        ]);

        $organization = Organization::create([
            'name' => 'Vote Org',
            'slug' => fake()->unique()->slug(),
            'owner_id' => $user->id,
        ]);

        $blueprint = Blueprint::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'organization_id' => $organization->id,
            'slug' => fake()->unique()->slug(),
            'title' => 'Test Blueprint',
            'is_public' => true,
            'tabs_config' => [],
            'created_by' => $user->id,
        ]);

        return [$user, $blueprint];
    }

    public function test_creates_upvote(): void
    {
        [$user, $blueprint] = $this->createUserAndBlueprint();

        $vote = Vote::create([
            'user_id' => $user->id,
            'blueprint_id' => $blueprint->id,
            'vote' => 1,
        ]);

        $this->assertNotNull($vote->id);
        $this->assertEquals($user->id, $vote->user_id);
        $this->assertEquals($blueprint->id, $vote->blueprint_id);
        $this->assertEquals(1, $vote->vote);
    }

    public function test_creates_downvote(): void
    {
        [$user, $blueprint] = $this->createUserAndBlueprint();

        $vote = Vote::create([
            'user_id' => $user->id,
            'blueprint_id' => $blueprint->id,
            'vote' => -1,
        ]);

        $this->assertEquals(-1, $vote->vote);
    }

    public function test_enforces_unique_user_blueprint_constraint(): void
    {
        [$user, $blueprint] = $this->createUserAndBlueprint();

        Vote::create([
            'user_id' => $user->id,
            'blueprint_id' => $blueprint->id,
            'vote' => 1,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Vote::create([
            'user_id' => $user->id,
            'blueprint_id' => $blueprint->id,
            'vote' => -1,
        ]);
    }

    public function test_vote_belongs_to_user(): void
    {
        [$user, $blueprint] = $this->createUserAndBlueprint();

        $vote = Vote::create([
            'user_id' => $user->id,
            'blueprint_id' => $blueprint->id,
            'vote' => 1,
        ]);

        $this->assertInstanceOf(User::class, $vote->user);
        $this->assertEquals($user->id, $vote->user->id);
    }

    public function test_vote_belongs_to_blueprint(): void
    {
        [$user, $blueprint] = $this->createUserAndBlueprint();

        $vote = Vote::create([
            'user_id' => $user->id,
            'blueprint_id' => $blueprint->id,
            'vote' => 1,
        ]);

        $this->assertInstanceOf(Blueprint::class, $vote->blueprint);
        $this->assertEquals($blueprint->id, $vote->blueprint->id);
    }
}
