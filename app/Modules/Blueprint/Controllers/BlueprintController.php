<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Actions\DeleteBlueprint;
use App\Modules\Blueprint\Actions\RestoreBlueprint;
use App\Modules\Blueprint\Actions\TransferBlueprint;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Organization\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlueprintController
{
    public function index(): View
    {
        return view('blueprint::index');
    }

    public function create(): View
    {
        return view('blueprint::create');
    }

    public function show(string $uuid): View
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
        return view('blueprint::show', compact('blueprint'));
    }

    public function edit(string $uuid): View
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
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
            ->with('organization')
            ->orderBy('deleted_at', 'desc')
            ->get();
        
        return view('blueprint::deleted', compact('deletedBlueprints'));
    }

    public function destroy(string $uuid, DeleteBlueprint $deleteBlueprint): RedirectResponse
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();
        
        // Authorize
        if (!auth()->user()->can('delete', $blueprint)) {
            abort(403, 'No tienes permisos para eliminar este blueprint.');
        }
        
        $deleteBlueprint->execute($blueprint);
        
        return redirect()
            ->route('organizations.show', $blueprint->organization->slug)
            ->with('success', 'Blueprint eliminado correctamente.');
    }

    public function restore(string $uuid, RestoreBlueprint $restoreBlueprint): RedirectResponse
    {
        $blueprint = Blueprint::withTrashed()->where('uuid', $uuid)->firstOrFail();
        
        // Authorize - only owner can restore
        if (!auth()->user()->isOwnerOf($blueprint->organization)) {
            abort(403, 'No tienes permisos para restaurar este blueprint.');
        }
        
        $restoreBlueprint->execute($blueprint);
        
        return redirect()
            ->route('blueprints.show', $blueprint->uuid)
            ->with('success', 'Blueprint restaurado correctamente.');
    }

    public function transfer(string $uuid, Request $request, TransferBlueprint $transferBlueprint): RedirectResponse
    {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'target_organization_id' => ['required', 'integer', 'exists:organizations,id'],
        ]);

        $targetOrganization = Organization::findOrFail($validated['target_organization_id']);

        $transferBlueprint->execute(
            blueprint: $blueprint,
            targetOrganization: $targetOrganization,
            user: auth()->user(),
        );

        return redirect()
            ->route('blueprints.show', $blueprint->fresh()->uuid)
            ->with('success', 'Blueprint transferido correctamente.');
    }
}
