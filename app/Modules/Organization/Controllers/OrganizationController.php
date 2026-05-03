<?php

declare(strict_types=1);

namespace App\Modules\Organization\Controllers;

use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Actions\UpdateOrganization;
use App\Modules\Organization\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        return view('organization::show', compact('organization'));
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
}
