<?php

declare(strict_types=1);

namespace App\Modules\Organization\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\CreateOrganizationUser;
use App\Modules\Organization\Actions\DeleteOrganization;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Actions\UpdateOrganization;
use App\Modules\Organization\Actions\UpdateOrganizationUserRole;
use App\Modules\Organization\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mail;

class OrganizationController
{
    public function index(): View
    {
        return view('organization::index');
    }

    public function create(): View
    {
        return view('organization::create');
    }

    public function show(string $slug): View
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        $plan = $organization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;
        $activeBlueprintsCount = $organization->blueprints()->count();
        $canCreateBlueprint = $maxBlueprints === null || $activeBlueprintsCount < $maxBlueprints;

        return view('organization::show', compact(
            'organization',
            'activeBlueprintsCount',
            'maxBlueprints',
            'canCreateBlueprint'
        ));
    }

    public function edit(string $slug): View
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('update', $organization)) {
            abort(403, 'No tienes permisos para editar esta organización.');
        }

        return view('organization::edit', compact('organization'));
    }

    public function update(string $slug, Request $request, UpdateOrganization $updateOrganization): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('update', $organization)) {
            abort(403, 'No tienes permisos para editar esta organización.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
        ]);

        $updateOrganization->execute(
            organization: $organization,
            name: $validated['name'],
            slug: $validated['slug'],
        );

        return redirect()
            ->route('organizations.show', $organization->fresh()->slug)
            ->with('success', 'Organización actualizada correctamente.');
    }

    public function members(string $slug): View
    {
        $organization = Organization::where('slug', $slug)
            ->with(['members', 'invitations'])
            ->firstOrFail();

        if (!auth()->user()->can('view', $organization)) {
            abort(403, 'No tienes permisos para ver esta organización.');
        }

        return view('organization::members', compact('organization'));
    }

    public function storeMember(string $slug, Request $request, CreateOrganizationUser $createOrganizationUser): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('manageMembers', $organization)) {
            abort(403, 'No tienes permisos para gestionar miembros.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:developer,maintainer'],
        ]);

        $user = $createOrganizationUser->execute(
            organization: $organization,
            name: $validated['name'],
            email: $validated['email'],
            role: $validated['role'],
        );

        // Enviar email de bienvenida con credenciales
        // Mail::to($user->email)->send(new \App\Modules\Organization\Mail\WelcomeToOrganization($user, $temporaryPassword ?? 'Cambia tu password al iniciar', $organization));

        return redirect()
            ->route('organizations.members', $organization->slug)
            ->with('success', "Usuario {$user->name} agregado correctamente.");
    }

    public function updateMemberRole(string $slug, int $userId, Request $request, UpdateOrganizationUserRole $updateRole): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'role' => ['required', 'in:developer,maintainer'],
        ]);

        $targetUser = User::findOrFail($userId);

        $updateRole->execute(
            organization: $organization,
            targetUser: $targetUser,
            newRole: $validated['role'],
            actor: auth()->user(),
        );

        return redirect()
            ->route('organizations.members', $organization->slug)
            ->with('success', "Rol de {$targetUser->name} actualizado correctamente.");
    }

    public function invite(string $slug, Request $request, InviteUser $inviteUser): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('invite', $organization)) {
            abort(403, 'No tienes permisos para invitar miembros.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:developer,maintainer'],
        ]);

        $inviteUser->execute(
            organization: $organization,
            email: $validated['email'],
            role: $validated['role'],
        );

        return redirect()
            ->route('organizations.members', $organization->slug)
            ->with('success', 'Invitación enviada correctamente.');
    }

    public function destroy(string $slug, DeleteOrganization $deleteOrganization): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('delete', $organization)) {
            abort(403, 'No tienes permisos para eliminar esta organización.');
        }

        // Soft delete de blueprints en cascada
        $organization->blueprints()->delete();

        $deleteOrganization->execute($organization);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Organización eliminada. Puedes recuperarla desde el dashboard.');
    }

    public function restore(string $slug): RedirectResponse
    {
        $organization = Organization::withTrashed()->where('slug', $slug)->firstOrFail();

        // Verificar que el usuario es owner de la org
        if (!auth()->user()->isOwnerOf($organization)) {
            abort(403, 'No tienes permisos para restaurar esta organización.');
        }

        // Verificar límite de organizaciones del plan
        $user = auth()->user();
        $plan = $user->plan;
        $activeOrgsCount = $user->organizations()->count();

        if ($plan->max_organizations_per_user !== null && $activeOrgsCount >= $plan->max_organizations_per_user) {
            return redirect()
                ->route('dashboard')
                ->with('error', "Límite de {$plan->max_organizations_per_user} organizaciones alcanzado. Elimina una organización activa para poder recuperar esta.");
        }

        // Restaurar org y sus blueprints
        $organization->blueprints()->restore();
        $organization->restore();

        return redirect()
            ->route('organizations.show', $organization->slug)
            ->with('success', 'Organización restaurada correctamente.');
    }

    public function forceDestroy(string $slug): RedirectResponse
    {
        $organization = Organization::withTrashed()->where('slug', $slug)->firstOrFail();

        if (!auth()->user()->isOwnerOf($organization)) {
            abort(403, 'No tienes permisos para eliminar permanentemente esta organización.');
        }

        // Hard delete de blueprints
        $organization->blueprints()->forceDelete();

        // Hard delete de invitaciones
        $organization->invitations()->forceDelete();

        // Hard delete de la org
        $organization->forceDelete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Organización eliminada permanentemente.');
    }
}