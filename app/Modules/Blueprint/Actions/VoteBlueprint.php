<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVote;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VoteBlueprint
{
    public function execute(Blueprint $blueprint, User $user, string $voteType): void
    {
        // 1. Defense-in-depth: validate vote type
        if (!in_array($voteType, ['up', 'down'], true)) {
            throw new \InvalidArgumentException('Invalid vote type. Must be "up" or "down".');
        }

        // 2. Check marketplace enabled (feature flag)
        if (!config('marketplace.enabled')) {
            throw new HttpException(503, __('blueprint.vote_marketplace_disabled'));
        }

        // 3. Blueprint must be public
        if (!$blueprint->is_public) {
            throw new HttpException(403, __('blueprint.vote_denied'));
        }

        // 3. Upsert vote
        BlueprintVote::updateOrCreate(
            [
                'user_id' => $user->id,
                'blueprint_id' => $blueprint->id,
            ],
            [
                'vote_type' => $voteType,
            ]
        );

        // 4. Recalculate aggregate score
        $score = BlueprintVote::where('blueprint_id', $blueprint->id)
            ->selectRaw("SUM(CASE WHEN vote_type = 'up' THEN 1 ELSE 0 END) - SUM(CASE WHEN vote_type = 'down' THEN 1 ELSE 0 END) as score")
            ->value('score');

        $blueprint->update(['aggregate_score' => $score ?? 0]);
    }
}
