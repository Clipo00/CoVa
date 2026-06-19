# Exploration: Security / Authorization Validation Audit

## Current State

CoVa is a Laravel 13 SaaS with modular architecture. Authorization is role-based (owner/maintainer/developer) via Policies and Action-level checks. Blueprints belong to Organizations. Plans define per-organization limits. The codebase has solid foundations (slug-based URLs, Policy gates, rate limiting, soft deletes) but several security validation gaps exist across role changes, CRUD boundaries, tab uniqueness, per-user limits, transfer logic, and registration/MFA.

---

## Affected Areas

- `app/Modules/Organization/Actions/UpdateOrganizationUserRole.php` — role change authorization
- `app/Modules/Organization/Actions/InviteUser.php` — invitation validation gaps
- `app/Modules/Organization/Actions/AcceptInvitation.php` — email mismatch gap
- `app/Modules/Blueprint/Policies/BlueprintPolicy.php` — developer delete permissions
- `app/Modules/Blueprint/Actions/TransferBlueprint.php` — missing target org limit check
- `app/Modules/Blueprint/Actions/CreateBlueprint.php` — limit is per-org not per-user
- `app/Modules/Blueprint/Livewire/Components/TabManager.php` — no duplicate tab prevention
- `app/Modules/Auth/Requests/RegisterRequest.php` — no disposable email or MFA
- `app/Modules/Auth/Actions/RegisterUser.php` — no email verification flow

---

## Approaches

### Option A: Patch each gap individually in existing files
- **Pros**: Fast, minimal file changes, low risk of regression
- **Cons**: Scattered fixes, harder to audit later, may miss related edge cases
- **Effort**: Medium

### Option B: Implement a centralized validation service + update all Actions
- **Pros**: Consistent rules, reusable, easier to test
- **Cons**: More initial work, requires refactoring existing Actions
- **Effort**: High

### Recommendation
**Option A** for immediate security fixes (critical/high gaps), with a follow-up task to extract reusable validators for limits and roles.

---

## Risks
- Point 1 (role change): Maintainer can currently change roles, which violates "only owner" requirement. If exploited, a maintainer could escalate themselves or others to owner-equivalent power.
- Point 4 (duplicate tabs): No validation means UI state can become inconsistent; downstream rendering may break or confuse users.
- Point 5 (per-user limit): Missing entirely; a user with 2 orgs could create 6 blueprints on Free plan (3 per org) with no ceiling.
- Point 6 (transfer limit): Could exceed target org plan limit silently after transfer, causing data/policy inconsistency.
- Point 7 (registration): No email verification means unverified accounts can use the platform; no disposable email blocking increases abuse surface.

---

## Detailed Findings

### Point 1: Validate permission to change user role

**Implemented**: PARTIAL

**Evidence**:
- `UpdateOrganizationUserRole::execute()` at lines 19-38:
  - Checks `$actor->canManageMembers($organization)` — this returns `true` for **both owner AND maintainer** (`User::canManageMembers()` line 137-141).
  - Checks target is not the owner.
  - Validates new role is `developer` or `maintainer`.
  - Validates target user is a member of the org.

**Missing**:
- Should restrict to **only owner** (not maintainer). The requirement says: "Only the organization owner can change another user's role."
- `OrganizationController::updateMemberRole()` (line 125-145) does **not** call `$this->authorize()` before invoking the Action. It relies solely on the Action's internal check.

**Risk Level**: **HIGH** — maintainer can change roles.

---

### Point 2: Validate permission to delete/update/soft-delete with user role "developer"

**Implemented**: PARTIAL (with policy drift)

**Evidence**:
- `BlueprintPolicy::update()` (line 23-27): allows creator OR owner/maintainer. Developer can update **their own** blueprint.
- `BlueprintPolicy::delete()` (line 29-33): allows **creator** OR owner. This means a developer CAN delete their own blueprint.
- The `covar-laravel-policy` SKILL.md pattern says: "Solo Owner puede eliminar" and "Developer solo puede editar sus propios blueprints". The actual code deviates by allowing the creator to delete.
- `DeleteBlueprint` action (line 11-14) just calls `$blueprint->delete()` (soft delete). No additional role check.
- `BlueprintController::destroy()` (line 157-171) checks `can('delete', $blueprint)` before executing.

**Missing**:
- If the intended design is that **only owner** can delete (per SKILL.md pattern), then `BlueprintPolicy::delete()` is too permissive.
- No hard-delete endpoint for blueprints at the blueprint level (only soft delete via `destroy()`). Organization force-delete hard-deletes blueprints in cascade.

**Risk Level**: **MEDIUM** — depends on intended design; if SKILL.md is source of truth, this is a HIGH drift.

---

### Point 3: Validate you can't add a user to an organization they don't own

**Implemented**: PARTIAL

**Evidence**:
- `OrganizationController::invite()` (line 147-169) checks `can('invite', $organization)` before calling `InviteUser`.
- `InviteUser::execute()` (line 14-29) creates invitation with no auth check (relies on controller).
- `AcceptInvitation::execute()` (line 13-52):
  - Validates token exists, is valid (not expired/used).
  - If `$user` is null, looks up by invitation email.
  - If `$user` is passed explicitly, it does **NOT** verify that `$user->email === $invitation->email`.
  - Attaches user to organization with invitation role.
- `OrganizationController::storeMember()` (line 96-123) checks `can('manageMembers', $organization)` before calling `CreateOrganizationUser`.
- `CreateOrganizationUser::execute()` (line 15-35) creates a new user and attaches to org. No check that the user already exists in another org (which is fine by design), but also no check on org member limit.

**Missing**:
- `AcceptInvitation` should verify that if a `$user` is explicitly passed, their email matches the invitation email.
- `CreateOrganizationUser` and `AcceptInvitation` do not check the organization's plan member limit (`max_members_per_org`).

**Risk Level**: **HIGH** — an attacker with a valid token could pass any authenticated user to `AcceptInvitation` and add them to an org.

---

### Point 4: Validate no duplicate tab types on a blueprint

**Implemented**: NO

**Evidence**:
- `TabManager::addTab()` (line 39-58): validates `TabType::isValid($type)` but does **not** check if a tab of that type already exists in `$this->tabs`.
- `BlueprintCreateForm::submit()` and `BlueprintEditForm::submit()` normalize tabs to `['type', 'config']` format but do **not** check for duplicate types.
- `Blueprint` model has no validation for `tabs_config` content.
- Migration `create_blueprints_table` stores `tabs_config` as JSON with no unique constraints.

**Missing**:
- Application-level deduplication in `TabManager::addTab()` or form submission.
- Optional: database-level enforcement is impractical for JSON arrays; application-level is correct.

**Risk Level**: **MEDIUM** — UX inconsistency, potential downstream rendering issues.

---

### Point 5: Validate max number of blueprints for user (not organization)

**Implemented**: NO

**Evidence**:
- `CreateBlueprint::execute()` (line 24-29) checks `max_blueprints_per_org` against `$organization->blueprints()->count()`.
- `Plan` model has `max_blueprints_per_org` and `max_organizations_per_user`, but **no `max_blueprints_per_user`**.
- `RestoreBlueprint::execute()` (line 12-26) also checks per-organization limit.
- All tests (`CreateBlueprintTest`, `RestoreBlueprintTest`) validate per-org limits only.

**Missing**:
- No per-user blueprint count limit anywhere in the codebase.
- On Free plan (2 orgs, 3 blueprints/org), a user could create 6 blueprints total with no ceiling.

**Risk Level**: **MEDIUM** — depends on product requirements; if per-user limit is intended, this is a gap.

---

### Point 6: Validate transfer blueprint with max number blueprint for receiver

**Implemented**: PARTIAL

**Evidence**:
- `TransferBlueprint::execute()` (line 13-42):
  - Validates actor is owner of source org.
  - Validates actor is owner of target org.
  - Validates orgs are different.
  - Validates slug uniqueness in target org.
  - Does **NOT** check if target organization has reached its `max_blueprints_per_org` limit.
- `BlueprintController::transfer()` (line 195-214) validates `target_organization_id` exists but does not check limits.

**Missing**:
- Check target org blueprint count against its plan limit before transfer.
- The requirement mentions "between users in the same organization" but the actual implementation transfers between **organizations** (and requires ownership of both). This is a design/requirement mismatch.

**Risk Level**: **HIGH** — could exceed plan limits silently, leading to billing/policy inconsistency.

---

### Point 7: Validate email registration

**Implemented**: PARTIAL

**Evidence**:
- `RegisterRequest::rules()` (line 14-21):
  - `email` => `required|string|email|max:255|unique:users`
  - No disposable/temporary email domain blocking.
  - No custom email validation beyond Laravel's default `email` rule.
- `RegisterUserData::__construct()` (line 17-39) uses Laravel Validator with same rules, then wraps in `Email` VO which calls `filter_var($email, FILTER_VALIDATE_EMAIL)`.
- `RegisterUser::execute()` (line 14-35) creates user, assigns Free plan. No email verification step.
- `users` table has `email_verified_at` (migration `0001_01_01_000000_create_users_table` line 18), but:
  - No verification email is sent.
  - No verification controller/route exists.
  - No middleware checking `email_verified_at`.
- No MFA infrastructure exists anywhere (no codes table, no MFA setup flow, no TOTP).

**Missing**:
- Disposable/temporary email domain blacklist validation.
- Email verification flow (send email, verify token, mark `email_verified_at`).
- MFA with email code verification.

**Risk Level**: **CRITICAL** for MFA (if required by security roadmap) / **HIGH** for email verification / **MEDIUM** for disposable emails.

---

## Critical Gaps

1. **Point 1 (HIGH)**: `UpdateOrganizationUserRole` allows maintainers to change roles. Should be restricted to owner only.
2. **Point 3 (HIGH)**: `AcceptInvitation` does not verify that explicitly passed user matches invitation email.
3. **Point 6 (HIGH)**: `TransferBlueprint` does not check target organization blueprint limit.
4. **Point 7 (CRITICAL/HIGH)**: No email verification flow, no MFA, no disposable email blocking.

---

## Next Recommended

- **sdd-propose** a security hardening change that covers points 1, 3, 4, 6 as immediate fixes.
- **sdd-spec** for point 7 (email verification + disposable email blocking) as a separate change due to scope.
- **sdd-spec** for point 5 (per-user blueprint limit) if product confirms this requirement.
- Run `php artisan test --filter=Policy` and `php artisan test --filter=Auth` after fixes.

---

## Skill Resolution

paths-injected — 4 skills (covar-security, covar-laravel-policy, covar-laravel-action, covar-laravel-model)
