<?php

declare(strict_types=1);

namespace App\Modules\Organization\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Organization\Actions\AcceptInvitation;
use App\Modules\Organization\Actions\CreateOrganizationUser;
use App\Modules\Organization\Actions\DeleteOrganization;
use App\Modules\Organization\Actions\InviteUser;
use App\Modules\Organization\Actions\RemoveOrganizationUser;
use App\Modules\Organization\Actions\ResendInvitation;
use App\Modules\Organization\Actions\RevokeInvitation;
use App\Modules\Organization\Actions\UpdateOrganization;
use App\Modules\Organization\Actions\UpdateOrganizationUserRole;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationInvitation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        if (!auth()->user()->can('view', $organization)) {
            abort(403, __('organization.no_view_permission'));
        }

        $plan = $organization->plan;
        $maxBlueprints = $plan->max_blueprints_per_org;
        $activeBlueprintsCount = $organization->blueprints()->count();
        $canCreateBlueprint = $maxBlueprints === null || $activeBlueprintsCount < $maxBlueprints;
        $publicBlueprintsCount = $organization->blueprints()->where('is_public', true)->count();

        return view('organization::show', compact(
            'organization',
            'activeBlueprintsCount',
            'maxBlueprints',
            'canCreateBlueprint',
            'publicBlueprintsCount'
        ));
    }

    public function edit(string $slug): View
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('update', $organization)) {
            abort(403, __('organization.no_edit_permission'));
        }

        return view('organization::edit', compact('organization'));
    }

    public function update(string $slug, Request $request, UpdateOrganization $updateOrganization): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('update', $organization)) {
            abort(403, __('organization.no_edit_permission'));
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
            ->with('success', __('organization.updated'));
    }

    public function members(string $slug): View
    {
        $organization = Organization::where('slug', $slug)
            ->with(['members', 'invitations'])
            ->firstOrFail();

        if (!auth()->user()->can('view', $organization)) {
            abort(403, __('organization.no_view_permission'));
        }

        return view('organization::members', compact('organization'));
    }

    public function storeMember(string $slug, Request $request, CreateOrganizationUser $createOrganizationUser): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('manageMembers', $organization)) {
            abort(403, __('organization.no_manage_permission'));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
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
            ->with('success', __('organization.user_added', ['name' => $user->name]));
    }

    public function updateMemberRole(string $slug, int $userId, Request $request, UpdateOrganizationUserRole $updateRole): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        $this->authorize('updateMemberRole', $organization);

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
            ->with('success', __('organization.role_updated', ['name' => $targetUser->name]));
    }

    /**
     * Show invitation — OWASP A01/A07: validates token, handles guest redirect flow.
     * Guests store token in session and redirect to login.
     * Authenticated users accept directly.
     */
    public function showInvitation(string $token): RedirectResponse
    {
        try {
            $invitation = OrganizationInvitation::where('token', $token)->firstOrFail();
        } catch (ModelNotFoundException) {
            return redirect()->route('login')
                ->with('error', __('organization.invitation_not_found'));
        }

        if (!$invitation->isValid()) {
            return redirect()->route('login')
                ->with('error', __('organization.invitation_expired'));
        }

        if (!auth()->check()) {
            session(['invitation_token' => $token]);

            return redirect()->guest(route('login'));
        }

        // Authenticated user: verify email match
        if (auth()->user()->email !== $invitation->email) {
            return redirect()->route('dashboard')
                ->with('error', __('organization.invitation_email_mismatch'));
        }

        try {
            app(AcceptInvitation::class)->execute($token, auth()->user());
        } catch (ValidationException $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('organizations.show', $invitation->organization->slug)
            ->with('success', __('organization.invitation_accepted'));
    }

    /**
     * Accept invitation via POST — CSRF protected endpoint for form-based acceptance.
     */
    public function acceptInvitation(string $token, Request $request, AcceptInvitation $acceptInvitation): RedirectResponse
    {
        try {
            $invitation = OrganizationInvitation::where('token', $token)->firstOrFail();
        } catch (ModelNotFoundException) {
            return redirect()->route('dashboard')
                ->with('error', __('organization.invitation_not_found'));
        }

        try {
            $acceptInvitation->execute($token, auth()->user());
        } catch (ValidationException $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('organizations.show', $invitation->organization->slug)
            ->with('success', __('organization.invitation_accepted'));
    }

    public function invite(string $slug, Request $request, InviteUser $inviteUser): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('invite', $organization)) {
            abort(403, __('organization.no_invite_permission'));
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
            ->with('success', __('organization.invite_sent'));
    }

    public function revokeInvitation(string $slug, int $invitationId, RevokeInvitation $revokeAction): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('revokeInvitation', $organization)) {
            abort(403, __('organization.no_revoke_permission'));
        }

        $invitation = $organization->invitations()->findOrFail($invitationId);

        $revokeAction->execute($invitation);

        return redirect()
            ->route('organizations.members', $organization->slug)
            ->with('success', __('organization.invitation_revoked'));
    }

    public function resendInvitation(string $slug, int $invitationId, ResendInvitation $resendAction): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('resendInvitation', $organization)) {
            abort(403, __('organization.no_resend_permission'));
        }

        $invitation = $organization->invitations()->findOrFail($invitationId);

        $resendAction->execute($invitation);

        return redirect()
            ->route('organizations.members', $organization->slug)
            ->with('success', __('organization.invitation_resent'));
    }

    public function removeMember(string $slug, int $userId, RemoveOrganizationUser $removeAction): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('removeMember', $organization)) {
            abort(403, __('organization.no_manage_permission'));
        }

        $targetUser = User::findOrFail($userId);

        $removeAction->execute(
            organization: $organization,
            targetUser: $targetUser,
            actor: auth()->user(),
        );

        return redirect()
            ->route('organizations.members', $organization->slug)
            ->with('success', __('organization.remove_member_success', ['name' => $targetUser->name]));
    }

    public function destroy(string $slug, DeleteOrganization $deleteOrganization): RedirectResponse
    {
        $organization = Organization::where('slug', $slug)->firstOrFail();

        if (!auth()->user()->can('delete', $organization)) {
            abort(403, __('organization.no_delete_permission'));
        }

        // Soft delete de blueprints en cascada
        $organization->blueprints()->delete();

        $deleteOrganization->execute($organization);

        return redirect()
            ->route('dashboard')
            ->with('success', __('organization.deleted'));
    }

    public function restore(string $slug): RedirectResponse
    {
        $organization = Organization::withTrashed()->where('slug', $slug)->firstOrFail();

        // Verificar que el usuario es owner de la org
        if (!auth()->user()->isOwnerOf($organization)) {
            abort(403, __('organization.no_restore_permission'));
        }

        // Verificar límite de organizaciones del plan
        $user = auth()->user();
        $plan = $user->plan;
        $activeOrgsCount = $user->organizations()->count();

        if ($plan->max_organizations_per_user !== null && $activeOrgsCount >= $plan->max_organizations_per_user) {
            return redirect()
                ->route('dashboard')
                ->with('error', __('organization.restore_limit', ['max' => $plan->max_organizations_per_user]));
        }

        // Restaurar org y sus blueprints
        $organization->blueprints()->restore();
        $organization->restore();

        return redirect()
            ->route('organizations.show', $organization->slug)
            ->with('success', __('organization.restored'));
    }

    public function forceDestroy(string $slug): RedirectResponse
    {
        $organization = Organization::withTrashed()->where('slug', $slug)->firstOrFail();

        if (!auth()->user()->isOwnerOf($organization)) {
            abort(403, __('organization.no_force_delete_permission'));
        }

        // Hard delete de blueprints
        $organization->blueprints()->forceDelete();

        // Hard delete de invitaciones
        $organization->invitations()->forceDelete();

        // Hard delete de la org
        $organization->forceDelete();

        return redirect()
            ->route('dashboard')
            ->with('success', __('organization.force_deleted'));
    }
}
