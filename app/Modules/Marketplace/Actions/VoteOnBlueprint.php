<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Models\Vote;

class VoteOnBlueprint
{
    /**
     * Execute a vote action (upvote/downvote/toggle/flip).
     *
     * @return array{votes_count: int, user_vote: int|null}
     */
    public function execute(User $user, Blueprint $blueprint, int $vote): array
    {
        $existing = Vote::where('user_id', $user->id)
            ->where('blueprint_id', $blueprint->id)
            ->first();

        if ($existing && $existing->vote === $vote) {
            // Toggle off: delete the vote
            $existing->delete();
            $blueprint->decrement('votes_count', $vote);

            return [
                'votes_count' => $blueprint->fresh()->votes_count,
                'user_vote' => null,
            ];
        }

        if ($existing && $existing->vote !== $vote) {
            // Flip: update the vote value and adjust count by 2x
            $existing->update(['vote' => $vote]);
            $blueprint->increment('votes_count', $vote * 2);

            return [
                'votes_count' => $blueprint->fresh()->votes_count,
                'user_vote' => $vote,
            ];
        }

        // No existing vote: create new one
        Vote::create([
            'user_id' => $user->id,
            'blueprint_id' => $blueprint->id,
            'vote' => $vote,
        ]);
        $blueprint->increment('votes_count', $vote);

        return [
            'votes_count' => $blueprint->fresh()->votes_count,
            'user_vote' => $vote,
        ];
    }
}
