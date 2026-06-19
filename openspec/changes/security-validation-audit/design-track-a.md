# Design: Security Validation Audit — Track A

## Technical Approach

Surgical patches to 5 authorization/validation gaps. Each fix is localized to a single file (or 2–3 tightly coupled files) with zero migrations. Changes align with existing CoVa conventions: Policy gates, Action-level limit checks, Livewire component validation, and PHPUnit tests.

## Architecture Decisions

| Decision | Option | Tradeoff | Rationale |
|----------|--------|----------|-----------|
| Role change restricted to owner only | `isOwnerOf()` | Maintainers lose member-management capability | Matches spec 1.1 and closes HIGH-risk elevation vector |
| Blueprint delete = owner only | Remove creator check from `delete()` | Developers can no longer delete their own blueprints | Aligns with `covar-laravel-policy` SKILL.md source of truth |
| Invitation limit exception | New `MaxMembersReachedException` | One new exception class | Keeps error messaging consistent with `MaxBlueprintsReachedException` pattern |
| Duplicate tabs rejected where | `TabManager` + form `submit()` | Double-validation is slightly redundant | Defense in depth: UI blocks immediate duplicates; server blocks tampered submissions |
| Transfer limit reuse | Reuse `MaxBlueprintsReachedException` | Exception lives in Blueprint module but semantically fits target-org limit | Avoids exception proliferation; same user-facing message |

## Data Flow

No new data flows or external services. All changes are synchronous in-request validations.

## File Changes

| File | Action | Description |
|------|--------|-------------|
| `app/Modules/Organization/Actions/UpdateOrganizationUserRole.php` | Modify | Replace `canManageMembers()` with `isOwnerOf()` |
| `app/Modules/Organization/Controllers/OrganizationController.php` | Modify | Add `$this->authorize('updateMemberRole', $organization)` in `updateMemberRole()` |
| `app/Modules/Organization/Policies/OrganizationPolicy.php` | Modify | Add `updateMemberRole()` gate |
| `app/Modules/Blueprint/Policies/BlueprintPolicy.php` | Modify | Remove creator check from `delete()` |
| `app/Modules/Organization/Actions/AcceptInvitation.php` | Modify | Add email-match guard + `max_members_per_org` check |
| `app/Modules/Organization/Exceptions/MaxMembersReachedException.php` | Create | Domain exception for org member limit |
| `app/Modules/Blueprint/Livewire/Components/TabManager.php` | Modify | Reject duplicate type in `addTab()`; expose `$tabError` |
| `app/Modules/Blueprint/Views/livewire/components/tab-manager.blade.php` | Modify | Render `$tabError` above add-buttons |
| `app/Modules/Blueprint/Livewire/Forms/BlueprintCreateForm.php` | Modify | Validate unique tab types in `submit()` |
| `app/Modules/Blueprint/Livewire/Forms/BlueprintEditForm.php` | Modify | Validate unique tab types in `submit()` |
| `app/Modules/Blueprint/Actions/TransferBlueprint.php` | Modify | Check target org blueprint count against plan limit |

## Interfaces / Contracts

### New Exception
```php
class MaxMembersReachedException extends \RuntimeException
{
    public function __construct(int $limit, string $planName)
    {
        parent::__construct(__('organization.max_members_reached', ['limit' => $limit, 'plan' => $planName]));
    }
}
```

### Policy Gate
```php
public function updateMemberRole(User $user, Organization $organization): bool
{
    return $user->isOwnerOf($organization);
}
```

### Duplicate Tab Guard (TabManager)
```php
public string $tabError = '';

public function addTab(string $type): void
{
    if (!TabType::isValid($type)) return;

    foreach ($this->tabs as $tab) {
        if ($tab['type'] === $type) {
            $this->tabError = __('blueprint.duplicate_tab_type', ['type' => $type]);
            return;
        }
    }
    $this->tabError = '';
    // ... existing logic
}
```

### Duplicate Tab Guard (Forms)
In `submit()` of both forms, before DB call:
```php
$tabTypes = array_column($this->tabsConfig, 'type');
$duplicates = array_diff_assoc($tabTypes, array_unique($tabTypes));
if (!empty($duplicates)) {
    $this->addError('tabsConfig', __('blueprint.duplicate_tab_type', ['type' => reset($duplicates)]));
    return;
}
```

## Testing Strategy

| Layer | What to Test | Approach |
|-------|-------------|----------|
| Unit | `UpdateOrganizationUserRole` | Maintainer gets 403; owner succeeds; self-change still blocked |
| Feature | `OrganizationController::updateMemberRole` | HTTP 403 when maintainer posts; 200 when owner posts |
| Unit | `OrganizationPolicy::updateMemberRole` | True for owner; false for maintainer/developer |
| Unit | `BlueprintPolicy::delete` | Owner=true; creator-developer=false; maintainer=false |
| Unit | `AcceptInvitation` | Mismatched email throws ValidationException; at-limit org throws `MaxMembersReachedException` |
| Unit | `TransferBlueprint` | Target org at limit throws `MaxBlueprintsReachedException` |
| Unit / Feature | `TabManager` + forms | Duplicate type rejected in component and form submit |

**No migrations required.**

## Migration / Rollout

No migration required. All changes are code-only.

## Open Questions

- [ ] Should `updateMemberRole` gate also block the owner from changing their own role (belt-and-suspenders), or is the Action-level check sufficient? **Decision**: Action already blocks self-change; keep it there to avoid duplicating business logic.
- [ ] For duplicate tab types: should we also validate on `UpdateBlueprint` Action level? **Decision**: Not in Track A; form validation is sufficient for immediate fix. Action-level validation can be added in Track B if product requires it.

## Risks

| Risk | Level | Mitigation |
|------|-------|------------|
| Maintainer workflows break (can no longer change roles) | Medium | Expected per spec; communicated in proposal |
| Developer workflows break (can no longer delete own blueprint) | Low | Aligns with documented SKILL.md convention; tests updated first |
| TabManager UI now shows error message that did not exist before | Low | Blade view change is additive only |
| Transfer limit check may reject valid transfers for orgs at limit | Low | Matches existing `CreateBlueprint` limit pattern; consistent UX |
