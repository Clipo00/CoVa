<?php

declare(strict_types=1);

namespace App\Modules\Organization\Middleware;

use App\Modules\Organization\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $organization = $request->route('organization') ?? $request->route('slug');

        if ($organization && is_string($organization)) {
            $organization = Organization::where('slug', $organization)->first();
        }

        if (! $organization) {
            abort(404, __('organization.not_found'));
        }

        $allowedRoles = explode('|', $roles);

        if (! auth()->user()->hasRoleInOrganization($organization, $allowedRoles)) {
            abort(403, __('organization.no_permission'));
        }

        return $next($request);
    }
}
