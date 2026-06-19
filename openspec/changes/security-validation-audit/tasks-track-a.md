# Tasks: Security Validation Audit — Track A

## Review Workload Forecast

Decision needed before apply: No
Chained PRs recommended: No
Chain strategy: pending
400-line budget risk: Low

| Field | Value |
|-------|-------|
| Estimated changed lines | ~280 |
| Files affected | 11 |
| Delivery strategy | ask-always |

## Phase 1: Foundation — Policies & Exception

- [x] 1.1 Create `MaxMembersReachedException` at `app/Modules/Organization/Exceptions/MaxMembersReachedException.php`. Pattern: `(int $limit, string $planName)` with `__('organization.max_members_reached', ...)`. Follow `MaxBlueprintsReachedException`.
- [x] 1.2 Add `updateMemberRole()` gate to `OrganizationPolicy`: `return $user->isOwnerOf($organization);` — owner-only.
- [x] 1.3 Restrict `BlueprintPolicy::delete()` to owner-only: remove `$blueprint->created_by === $user->id` clause, keeping only `$user->isOwnerOf($blueprint->organization)`.

## Phase 2: Core Authorization Fixes

- [x] 2.1 Replace `$actor->canManageMembers()` with `$actor->isOwnerOf()` in `UpdateOrganizationUserRole::execute()` line 19.
- [x] 2.2 Add `$this->authorize('updateMemberRole', $organization)` at top of `OrganizationController::updateMemberRole()` (after the existing `findOrFail`, before the Action call).
- [x] 2.3 In `AcceptInvitation::execute()`, after line 43: if `$user` passed explicitly, require `$user->email === $invitation->email` or throw `ValidationException`.
- [x] 2.4 In `AcceptInvitation::execute()`, after email match: if `$organization->plan->max_members_per_org` not null and `$organization->members()->count() >= $limit`, throw `MaxMembersReachedException($limit, $plan->name)`.
- [x] 2.5 In `TransferBlueprint::execute()`, after slug uniqueness check (line 37): if `$targetOrganization->blueprints()->count() >= ($targetOrganization->plan->max_blueprints_per_org ?? PHP_INT_MAX)`, throw `MaxBlueprintsReachedException`.

## Phase 3: Duplicate Tab Prevention

- [x] 3.1 Add `public string $tabError = ''` to `TabManager`. In `addTab()`, before existing logic: loop `$this->tabs`, set `$tabError` if type exists, clear on success.
- [x] 3.2 Render `@if($tabError)` error alert above "Add Tab" buttons in `tab-manager.blade.php`. Use danger-alert pattern with `{{ $tabError }}`.
- [x] 3.3 In `BlueprintCreateForm::submit()`, after `$this->validate()`: detect duplicate `type` values in `$this->tabsConfig` via `array_column` + `array_unique`, call `$this->addError('tabsConfig', ...)`.
- [x] 3.4 Apply identical duplicate-type validation in `BlueprintEditForm::submit()`.

## Phase 4: Testing

- [x] 4.1 Add to `OrganizationPolicyTest`: `test_owner_can_update_member_role` (true), `test_maintainer_cannot_update_member_role` (false).
- [x] 4.2 Add to `BlueprintPolicyTest`: `test_creator_developer_cannot_delete_own_blueprint` (false), `test_maintainer_cannot_delete` (false).
- [x] 4.3 Create `AcceptInvitationTest` at `app/Modules/Organization/Tests/Unit/Actions/`. Test: email mismatch denied, expired denied, valid accepted, org-at-limit denied.
- [x] 4.4 Create `TransferBlueprintTest` at `app/Modules/Blueprint/Tests/Unit/Actions/`. Test: target-at-limit throws `MaxBlueprintsReachedException`, under-limit succeeds.
- [x] 4.5 Write Livewire test for `TabManager` duplicate rejection. Test form duplicate in `BlueprintCreateFormTest`.
- [x] 4.6 Run `php artisan test` — verify zero regressions across full suite.

### Dependencies

```
1.1 ──→ 2.4
1.2 ──→ 2.2
1.3 ──(independent)
2.1, 2.2 depend on 1.2
2.3, 2.4 depend on 1.1
2.5 independent (reuses existing MaxBlueprintsReachedException)
3.1 ──→ 3.2
3.3, 3.4 independent of Phase 1–2
Phase 4 after all GREEN tasks
```

### Verification

- `php artisan test --filter=OrganizationPolicyTest` — new gates pass
- `php artisan test --filter=BlueprintPolicyTest` — delete owner-only passes
- `php artisan test --filter=AcceptInvitationTest` — email match + limit
- `php artisan test --filter=TransferBlueprintTest` — target limit check
- Manual: try adding two `config` tabs via UI → error displayed
