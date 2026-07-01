<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Controllers\Api;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\ResolveBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlueprintApiController
{
    /**
     * Paginated, org-scoped, plan-gated listing of blueprints.
     *
     * Returns blueprints from all organizations the authenticated user
     * belongs to. Plan gating is handled by EnsureApiAccess middleware.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $orgIds = $user->organizations()->pluck('organizations.id');

        $perPage = min((int) ($request->query('per_page', 15)), 100);
        $perPage = max($perPage, 1);

        $blueprints = Blueprint::whereIn('organization_id', $orgIds)
            ->with('organization')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $data = $blueprints->map(function (Blueprint $blueprint) {
            return [
                'uuid' => $blueprint->uuid,
                'slug' => $blueprint->slug,
                'title' => $blueprint->title,
                'description' => $blueprint->description,
                'organization' => $blueprint->organization ? [
                    'slug' => $blueprint->organization->slug,
                    'name' => $blueprint->organization->name,
                ] : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $blueprints->currentPage(),
                'last_page' => $blueprints->lastPage(),
                'per_page' => $blueprints->perPage(),
                'total' => $blueprints->total(),
            ],
        ]);
    }

    /**
     * Show a fully resolved blueprint by slug.
     *
     * Resolves the blueprint via ResolveBlueprint action and returns
     * the output as JSON (with secret variable values masked).
     * Returns 404 if the blueprint does not belong to any of the
     * authenticated user's organizations (prevents org-bypass).
     */
    public function show(string $slug, ResolveBlueprint $resolveBlueprint, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $blueprint = Blueprint::where('slug', $slug)->first();

        if (!$blueprint) {
            return response()->json([
                'type' => config('app.url') . '/errors/not-found',
                'title' => 'Not Found',
                'status' => 404,
                'detail' => 'Blueprint not found.',
            ], 404);
        }

        // Verify blueprint belongs to one of the user's organizations
        $orgIds = $user->organizations()->pluck('organizations.id');

        if (!$blueprint->organization_id || !$orgIds->contains($blueprint->organization_id)) {
            return response()->json([
                'type' => config('app.url') . '/errors/not-found',
                'title' => 'Not Found',
                'status' => 404,
                'detail' => 'Blueprint not found.',
            ], 404);
        }

        $output = $resolveBlueprint->execute($blueprint);

        return response()->json($output->toApiArray());
    }
}
