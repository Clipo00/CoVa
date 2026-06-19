# Proposal: Security Validation Audit

## Intent

Close 7 authorization and validation gaps identified across role changes, blueprint CRUD, invitations, tab uniqueness, per-user limits, transfers, and registration security.

## Scope

### In Scope
- **Track A** — Immediate fixes: restrict role changes to owner-only, align BlueprintPolicy::delete() with SKILL.md convention, verify invitation email match, prevent duplicate tab types, enforce target org limit on blueprint transfer.
- **Track B** — Feature additions: per-user blueprint limit (`max_blueprints_per_user`), email verification flow, disposable/temporary email blocking, MFA infrastructure with email code verification.

### Out of Scope
- Audit logging (A09) — deferred per security roadmap
- CSP/HSTS refinements — already implemented
- Rate limit threshold tuning — deferred

## Capabilities

### New Capabilities
- `per-user-blueprint-limit`: Enforce `max_blueprints_per_user` across all orgs a user belongs to.
- `email-verification`: Send verification email, verify signed token, mark `email_verified_at`.
- `disposable-email-blocking`: Reject registration from known disposable/temporary domains.
- `mfa-email-code`: Infrastructure for MFA with email-based TOTP-like codes.
- `duplicate-tab-prevention`: Reject duplicate tab types in `TabManager` and form submissions.

### Modified Capabilities
- `role-management`: Restrict `UpdateOrganizationUserRole` to owner-only; add `$this->authorize()` in controller.
- `blueprint-deletion`: Change `BlueprintPolicy::delete()` to owner-only (remove creator check).
- `invitation-acceptance`: Verify passed user email matches invitation; enforce plan member limit.
- `blueprint-transfer`: Check target organization `max_blueprints_per_org` before transfer.

## Approach

**Track A**: Surgical patches — modify existing Actions, Policies, and Controllers. Add targeted tests. Low regression risk.

**Track B**: Add `max_blueprints_per_user` to Plan model + migration, update `CreateBlueprint` and `RestoreBlueprint`. Build email verification with signed URLs, disposable domain blacklist, and MFA tables/codes flow.

## Affected Areas

| Area | Impact | Description |
|------|--------|-------------|
| `Organization/Actions/UpdateOrganizationUserRole.php` | Modified | Check `isOwner()` instead of `canManageMembers()` |
| `Organization/Controllers/OrganizationController.php` | Modified | Add `$this->authorize()` in `updateMemberRole()` |
| `Blueprint/Policies/BlueprintPolicy.php` | Modified | Remove creator from `delete()` — owner only |
| `Organization/Actions/AcceptInvitation.php` | Modified | Email match check + plan member limit |
| `Blueprint/Livewire/Components/TabManager.php` | Modified | Reject duplicate tab types in `addTab()` |
| `Blueprint/Actions/TransferBlueprint.php` | Modified | Check target org blueprint limit |
| `Shared/Models/Plan.php` + migration | Modified | Add `max_blueprints_per_user` column |
| `Blueprint/Actions/CreateBlueprint.php` | Modified | Add per-user limit check |
| `Blueprint/Actions/RestoreBlueprint.php` | Modified | Add per-user limit check |
| `Auth/Actions/RegisterUser.php` | Modified | Trigger email verification |
| `Auth/Requests/RegisterRequest.php` | Modified | Add disposable email validation |
| `Auth/` | New | Verification controller, routes, MFA tables |

## Risks

| Risk | Likelihood | Mitigation |
|------|------------|------------|
| Changing `BlueprintPolicy::delete()` breaks existing developer workflows | Med | Confirm with stakeholders; update tests before code |
| Email verification flow blocks new registrations | Med | Allow grace period or make verification optional initially |
| Per-user limit reduces valid Free-tier usage | Low | Set `max_blueprints_per_user` = null (unlimited) for Free/Pro if product disagrees |

## Rollback Plan

- Revert Policy changes via git revert
- Disable email verification via feature flag or route middleware exclusion
- Remove `max_blueprints_per_user` column with migration rollback

## Dependencies

- `covar-security`, `covar-laravel-policy`, `covar-laravel-action`, `covar-laravel-model`, `covar-laravel-test` skills loaded
- Disposable email domain list (e.g., `disposable-email-domains` package or custom list)

## Success Criteria

- [ ] `php artisan test --filter=Policy` passes with new assertions
- [ ] `php artisan test --filter=Auth` passes with verification tests
- [ ] Maintainer cannot change roles in OrganizationControllerTest
- [ ] Developer cannot delete own blueprint in BlueprintPolicyTest
- [ ] Duplicate tab type rejected in TabManager/Blueprint form tests
- [ ] TransferBlueprintTest asserts target org limit enforcement
- [ ] CreateBlueprintTest and RestoreBlueprintTest cover per-user limit
- [ ] RegisterRequestTest rejects disposable domains
- [ ] Email verification flow tested end-to-end
