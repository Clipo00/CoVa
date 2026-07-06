<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVote;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VoteBlueprint
{
    public function execute(Blueprint $blueprint, User $user, int $vote): void
    {
        // 1. Defense-in-depth: validate vote value
        if (!in_array($vote, [1, -1], true)) {
            throw new \InvalidArgumentException(__('blueprint.vote_invalid'));
        }

        // 2. Check marketplace enabled (feature flag)
        if (!config('marketplace.enabled')) {
            throw new HttpException(503, __('blueprint.vote_marketplace_disabled'));
        }

        // 3. Blueprint must be public
        if (!$blueprint->is_public) {
            throw new HttpException(403, __('blueprint.vote_denied'));
        }

        // 4. Upsert vote
        BlueprintVote::updateOrCreate(
            [
                'user_id' => $user->id,
                'blueprint_id' => $blueprint->id,
            ],
            [
                'vote' => $vote,
            ]
        );

        // 5. Recalculate aggregate score (sum of all votes)
        $score = BlueprintVote::where('blueprint_id', $blueprint->id)->sum('vote');

        $blueprint->update(['aggregate_score' => $score]);
    }
}
