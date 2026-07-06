<?php

declare(strict_types=1);

namespace App\Modules\Auth\Middleware;

use App\Modules\Auth\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAccess
{
    /**
     * Handle an incoming request.
     *
     * Checks that the authenticated user has API access (Pro plan or higher).
     * Returns RFC 7807 Problem Details JSON on failure.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user || !$user->hasApiAccess()) {
            return response()->json([
                'type' => config('app.url') . '/errors/forbidden',
                'title' => 'Forbidden',
                'status' => 403,
                'detail' => 'Your current plan does not include API access. Please upgrade to Pro or Enterprise.',
            ], 403);
        }

        return $next($request);
    }
}
