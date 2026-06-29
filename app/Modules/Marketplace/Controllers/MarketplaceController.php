<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\ResolveBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Actions\SubscribeToBlueprint;
use App\Modules\Marketplace\Actions\VoteOnBlueprint;
use App\Modules\Marketplace\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MarketplaceController
{
    public function index(): View
    {
        return view('marketplace::index');
    }

    public function show(string $uuid, ResolveBlueprint $resolveBlueprint): View
    {
        $blueprint = Blueprint::where('uuid', $uuid)
            ->where('is_public', true)
            ->with(['tags', 'organization.owner', 'variables'])
            ->firstOrFail();

        /** @var User|null $user */
        $user = auth()->user();

        // Resolve tabs
        $output = $resolveBlueprint->execute($blueprint);

        // Determine if the current user can view secret variable values
        $canViewSecrets = $user !== null && $blueprint->organization->owner_id === $user->id;

        // Get user's current vote if authenticated
        $userVote = null;
        if ($user !== null) {
            $vote = Vote::where('user_id', $user->id)
                ->where('blueprint_id', $blueprint->id)
                ->first();
            $userVote = $vote?->vote;
        }

        return view('marketplace::show', [
            'blueprint' => $blueprint,
            'blueprintOutput' => $output,
            'canViewSecrets' => $canViewSecrets,
            'userVote' => $userVote,
        ]);
    }

    public function subscribe(Blueprint $blueprint): RedirectResponse
    {
        // Only public blueprints can be subscribed
        if (!$blueprint->is_public) {
            abort(404);
        }

        Gate::authorize('marketplace.subscribe', $blueprint);

        /** @var User $user */
        $user = auth()->user();

        $copy = app(SubscribeToBlueprint::class)->execute($user, $blueprint);

        return redirect()
            ->route('blueprints.edit', $copy->uuid)
            ->with('success', __('marketplace.copied_success'));
    }

    public function vote(Request $request, Blueprint $blueprint): JsonResponse
    {
        // Only public blueprints can be voted on
        if (!$blueprint->is_public) {
            abort(404);
        }

        $validated = $request->validate([
            'vote' => 'required|integer|in:1,-1',
        ]);

        Gate::authorize('marketplace.vote', $blueprint);

        /** @var User $user */
        $user = auth()->user();

        $result = app(VoteOnBlueprint::class)->execute($user, $blueprint, (int) $validated['vote']);

        return response()->json($result);
    }
}
