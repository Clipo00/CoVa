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
     * This middleware is only applied to specific route groups
     * (dashboard, organizations, blueprints) — NOT globally.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse|StreamedResponse
    {
        $user = $request->user();

        if ($user && $user->password_change_required) {
            if (!$request->routeIs('password.change')) {
                return redirect()->guest(route('password.change'));
            }
        }

        return $next($request);
    }
}
