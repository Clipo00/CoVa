<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\CreateBlueprint;
use App\Modules\Blueprint\Actions\VoteBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVote;
use App\Modules\Organization\Actions\CreateOrganization;
use App\Modules\Organization\Models\Organization;
use App\Modules\Shared\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class VoteBlueprintTest extends TestCase
{
    use RefreshDatabase;

    private VoteBlueprint $action;
    private Organization $organization;
    private User $owner;
    private Blueprint $publicBlueprint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PlanSeeder::class);

        $this->action = new VoteBlueprint();

        config(['marketplace.enabled' => true]);

        $proPlan = Plan::where('slug', 'pro')->first();
        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);

        $createOrg = new CreateOrganization();
        $this->organization = $createOrg->execute($this->owner, 'Test Org', 'test-org');

        $this->actingAs($this->owner);
        $createBp = new CreateBlueprint();
        $this->publicBlueprint = $createBp->execute(
            organization: $this->organization,
            title: 'Public BP',
            slug: 'public-bp',
        );
        $this->publicBlueprint->update(['is_public' => true]);
    }

    public function test_upvote_records_vote(): void
    {
        $this->action->execute($this->publicBlueprint, $this->owner, 1);

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $this->owner->id,
            'blueprint_id' => $this->publicBlueprint->id,
            'vote' => 1,
        ]);
    }

    public function test_downvote_records_vote(): void
    {
        $this->action->execute($this->publicBlueprint, $this->owner, -1);

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $this->owner->id,
            'blueprint_id' => $this->publicBlueprint->id,
            'vote' => -1,
        ]);
    }

    public function test_duplicate_flip_updates_existing_vote(): void
    {
        // First vote up
        $this->action->execute($this->publicBlueprint, $this->owner, 1);

        // Then flip to down
        $this->action->execute($this->publicBlueprint, $this->owner, -1);

        $this->assertDatabaseHas('blueprint_votes', [
            'user_id' => $this->owner->id,
            'blueprint_id' => $this->publicBlueprint->id,
            'vote' => -1,
        ]);

        // Should be only one vote
        $this->assertEquals(1, BlueprintVote::where('user_id', $this->owner->id)
            ->where('blueprint_id', $this->publicBlueprint->id)
            ->count());
    }

    public function test_non_public_blueprint_denied(): void
    {
        $this->actingAs($this->owner);
        $createBp = new CreateBlueprint();
        $privateBlueprint = $createBp->execute(
            organization: $this->organization,
            title: 'Private BP',
            slug: 'private-bp',
        );

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(__('blueprint.vote_denied'));

        $this->action->execute($privateBlueprint, $this->owner, 1);
    }

    public function test_marketplace_disabled_denies(): void
    {
        config(['marketplace.enabled' => false]);

        $this->expectException(HttpException::class);

        $this->action->execute($this->publicBlueprint, $this->owner, 1);
    }

    public function test_aggregate_score_is_calculated(): void
    {
        // Create additional users
        $proPlan = Plan::where('slug', 'pro')->first();
        $user2 = User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);
        $this->organization->members()->attach($user2->id, ['role' => 'developer']);

        $user3 = User::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);
        $this->organization->members()->attach($user3->id, ['role' => 'developer']);

        // Owner upvotes, User2 upvotes, User3 downvotes => score = 1
        $this->action->execute($this->publicBlueprint, $this->owner, 1);
        $this->action->execute($this->publicBlueprint, $user2, 1);
        $this->action->execute($this->publicBlueprint, $user3, -1);

        $this->publicBlueprint->refresh();
        $this->assertEquals(1, $this->publicBlueprint->aggregate_score);
    }
}
