<?php

declare(strict_types=1);

namespace App\Modules\Auth\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureOnboardingCompleted
{
    /**
     * Redirect un-onboarded users to the onboarding wizard.
     *
     * If the authenticated user has not completed onboarding
     * (onboarding_completed_at IS NULL), they are redirected
     * to the onboarding wizard. Onboarded users pass through.
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->onboarding_completed_at === null) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
