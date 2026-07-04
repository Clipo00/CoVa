<?php

declare(strict_types=1);

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnsurePasswordNotTemporary
{
    /**
     * Redirect users with temporary passwords to the password change page.
     *
     * Users created via invitation have a random temporary password
     * and must set their own before accessing any other page.
     *
     * Only applied to page routes — downloads, API, and Livewire are excluded.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse|StreamedResponse
    {
        $user = $request->user();

        if ($user && $user->password_change_required) {
            // Skip middleware for non-page routes to avoid breaking downloads, APIs, etc.
            if ($this->isExcludedRoute($request)) {
                return $next($request);
            }

            if (!$request->routeIs('password.change')) {
                return redirect()->guest(route('password.change'));
            }
        }

        return $next($request);
    }

    /**
     * Determine if the current route should bypass the password change check.
     */
    private function isExcludedRoute(Request $request): bool
    {
        // Allow POST routes (download ZIP, logout, Livewire updates)
        if ($request->isMethod('POST')) {
            return true;
        }

        // Allow API routes
        if ($request->is('api/*')) {
            return true;
        }

        return false;
    }
}
