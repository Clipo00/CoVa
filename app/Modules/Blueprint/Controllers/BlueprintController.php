<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\DeleteBlueprint;
use App\Modules\Blueprint\Actions\GenerateEnvTemplate;
use App\Modules\Blueprint\Actions\PublishBlueprint;
use App\Modules\Blueprint\Actions\ResolveBlueprint;
use App\Modules\Blueprint\Actions\RestoreBlueprint;
use App\Modules\Blueprint\Actions\TransferBlueprint;
use App\Modules\Blueprint\Actions\VoteBlueprint;
use App\Modules\Blueprint\DTOs\AiContextConfig;
use App\Modules\Blueprint\DTOs\TabConfig;
use App\Modules\Blueprint\Exceptions\MaxBlueprintsReachedException;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Organization\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlueprintController
{
    public function index(): View
    {
        /** @var User $user */
        $user = auth()->user();

        // Verificar si el usuario tiene organizaciones
        $userHasOrganizations = $user->organizations()->exists();

        // Verificar si el usuario tiene al menos 1 organización con cupo para blueprints
        $hasAvailableOrg = $user->organizations()
            ->with('owner.plan')
            ->get()
            ->contains(function ($organization) {
                $maxBlueprints = $organization->plan->max_blueprints_per_org;
                $activeCount = $organization->blueprints()->count();

                return $maxBlueprints === null || $activeCount < $maxBlueprints;
            });

        // Total de blueprints del usuario para el badge del heading
        $orgIds = $user->organizations()->pluck('organizations.id');
        $totalBlueprints = Blueprint::whereIn('organization_id', $orgIds)->count();

        return view('blueprint::index', compact(
            'hasAvailableOrg',
            'userHasOrganizations',
            'totalBlueprints'
        ));
    }

    public function create(): View|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        // Obtener organizaciones del usuario con info de disponibilidad
        $userOrganizations = $user->organizations()
            ->with('owner.plan')
            ->get()
            ->map(function ($organization) {
                $maxBlueprints = $organization->plan->max_blueprints_per_org;
                $activeCount = $organization->blueprints()->count();

                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'slug' => $organization->slug,
                    'hasAvailableSlots' => $maxBlueprints === null || $activeCount < $maxBlueprints,
                ];
            })
            ->values()
            ->all();

        $requestedOrgSlug = request('org');
        $preselectedOrg = null;
        $lockOrganization = false;

        if ($requestedOrgSlug) {
            // Validar que la org solicitada pertenece al usuario
            $requestedOrg = collect($userOrganizations)->firstWhere('slug', $requestedOrgSlug);

            if (!$requestedOrg) {
                abort(403, __('blueprint.org_unauthorized'));
            }

            // Si la org específica no tiene cupo, redirigir a esa org con mensaje de error
            if (!$requestedOrg['hasAvailableSlots']) {
                return redirect()
                    ->route('organizations.show', $requestedOrg['slug'])
                    ->with('error', __('blueprint.org_limit'));
            }

            $preselectedOrg = $requestedOrg['id'];
            $lockOrganization = true;
        }

        // Si no se pidió org específica, verificar que al menos tiene 1 org con cupo
        if (!$requestedOrgSlug) {
            $hasAnyAvailable = collect($userOrganizations)->contains('hasAvailableSlots', true);

            if (!$hasAnyAvailable) {
                return redirect()
                    ->route('dashboard')
                    ->with('error', __('blueprint.no_capacity'));
            }
        }

        return view('blueprint::create', compact(
            'userOrganizations',
            'preselectedOrg',
            'lockOrganization'
        ));
    }

    public function show(
        Blueprint $blueprint,
        ResolveBlueprint $resolveBlueprint,
        AgentGenerator $agentGenerator,
        GenerateEnvTemplate $envTemplate,
    ): View {
        if (!auth()->user()->can('view', $blueprint)) {
            abort(403);
        }
        $output = $resolveBlueprint->execute($blueprint);

        // Resolve AI Context segments for download
        $segments = [];
        $envTemplateString = '';

        $tabsConfig = $blueprint->tabs_config ?? [];
        if (is_array($tabsConfig)) {
            foreach ($tabsConfig as $tabData) {
                if (!is_array($tabData)) {
                    continue;
                }

                try {
                    $tabConfig = TabConfig::fromArray($tabData);
                } catch (\InvalidArgumentException) {
                    continue;
                }

                if ($tabConfig->type->value === 'ai_context') {
                    $aiConfig = AiContextConfig::fromArray($tabConfig->config);
                    $segments = $agentGenerator->resolveSegments($aiConfig);
                }
            }
        }

        $envTemplateString = $envTemplate->execute($blueprint);

        return view('blueprint::show', [
            'blueprint' => $blueprint,
            'blueprintOutput' => $output,
            'segments' => $segments,
            'envTemplate' => $envTemplateString,
        ]);
    }

    public function edit(Blueprint $blueprint): View
    {
        if (!auth()->user()->can('update', $blueprint)) {
            abort(403, __('blueprint.no_edit_permission'));
        }

        return view('blueprint::edit', compact('blueprint'));
    }

    public function favorites(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $favoriteBlueprints = $user->favoriteBlueprints()->with('organization')->get();

        return view('blueprint::favorites', compact('favoriteBlueprints'));
    }

    public function deleted(): View
    {
        /** @var User $user */
        $user = auth()->user();

        // Obtener blueprints eliminados de organizaciones donde el user es miembro
        $organizationIds = $user->organizations()->pluck('organizations.id');

        $deletedBlueprints = Blueprint::onlyTrashed()
            ->whereIn('organization_id', $organizationIds)
            ->with('organization.owner.plan')
            ->orderBy('deleted_at', 'desc')
            ->get();

        // Precargar conteo de blueprints activos por organización (evita N+1)
        $activeBlueprintCounts = Blueprint::whereIn('organization_id', $organizationIds)
            ->whereNull('deleted_at')
            ->selectRaw('organization_id, COUNT(*) as count')
            ->groupBy('organization_id')
            ->pluck('count', 'organization_id');

        return view('blueprint::deleted', compact('deletedBlueprints', 'activeBlueprintCounts'));
    }

    public function destroy(string $uuid, DeleteBlueprint $deleteBlueprint): RedirectResponse
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();

        // Authorize
        if (!auth()->user()->can('delete', $blueprint)) {
            abort(403, __('blueprint.no_delete_permission'));
        }

        $deleteBlueprint->execute($blueprint);

        return redirect()
            ->route('organizations.show', $blueprint->organization->slug)
            ->with('success', __('blueprint.deleted_success'));
    }

    public function restore(string $uuid, RestoreBlueprint $restoreBlueprint): RedirectResponse
    {
        $blueprint = Blueprint::withTrashed()->where('uuid', $uuid)->firstOrFail();

        // Authorize - only owner can restore
        if (!auth()->user()->isOwnerOf($blueprint->organization)) {
            abort(403, __('blueprint.no_restore_permission'));
        }

        try {
            $restoreBlueprint->execute($blueprint);
        } catch (MaxBlueprintsReachedException $e) {
            return redirect()
                ->route('blueprints.deleted')
                ->with('error', __('blueprint.restore_limit_msg', ['message' => $e->getMessage()]));
        }

        return redirect()
            ->route('blueprints.show', $blueprint->slug)
            ->with('success', __('blueprint.restored_success'));
    }

    public function publish(string $uuid, PublishBlueprint $publishBlueprint): RedirectResponse
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();

        if (!auth()->user()->can('publish', $blueprint)) {
            abort(403, __('blueprint.publish_denied'));
        }

        $publishBlueprint->execute($blueprint, auth()->user());

        return redirect()
            ->route('blueprints.show', $blueprint->slug)
            ->with('success', __('blueprint.publish_success'));
    }

    public function vote(string $uuid, Request $request, VoteBlueprint $voteBlueprint): RedirectResponse
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'vote_type' => ['required', 'string', 'in:up,down'],
        ]);

        if (!auth()->user()->can('vote', $blueprint)) {
            abort(403, __('blueprint.vote_denied'));
        }

        $voteValue = $validated['vote_type'] === 'up' ? 1 : -1;
        $voteBlueprint->execute($blueprint, auth()->user(), $voteValue);

        return redirect()
            ->route('blueprints.show', $blueprint->slug)
            ->with('success', __('blueprint.vote_registered'));
    }

    public function transfer(string $uuid, Request $request, TransferBlueprint $transferBlueprint): RedirectResponse
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
        if (!auth()->user()->can('update', $blueprint)) {
            abort(403, __('blueprint.no_edit_permission'));
        }

        $validated = $request->validate([
            'target_organization_id' => ['required', 'integer', 'exists:organizations,id'],
        ]);

        $targetOrganization = Organization::findOrFail($validated['target_organization_id']);

        // User must be owner of the target organization
        if (!auth()->user()->isOwnerOf($targetOrganization)) {
            abort(403, __('blueprint.transfer_denied'));
        }

        $transferBlueprint->execute(
            blueprint: $blueprint,
            targetOrganization: $targetOrganization,
            user: auth()->user(),
        );

        return redirect()
            ->route('blueprints.show', $blueprint->fresh()->slug)
            ->with('success', __('blueprint.transferred_success'));
    }
}
