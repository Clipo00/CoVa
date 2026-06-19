# Delta Spec: Security Validation Audit

## MODIFIED: Role Change Authorization

| Req | Rule |
|-----|------|
| 1.1 | Only organization owner MAY change another member's role. |
| 1.2 | Target user MUST belong to the same organization. |
| 1.3 | Owner MUST NOT change their own role. |
| 1.4 | New role MUST be `developer` or `maintainer`. |

### Scenarios
- **Maintainer denied**: GIVEN a maintainer and a developer in the same org, WHEN the maintainer attempts to change the developer's role, THEN the action is denied with 403.
- **Owner allowed**: GIVEN an owner and a developer in the same org, WHEN the owner changes the developer to maintainer, THEN the role is updated.
- **Non-member denied**: GIVEN an owner, WHEN attempting to change the role of a user not in the org, THEN the action is denied with 403.
- **Self-change denied**: GIVEN an owner, WHEN attempting to change their own role, THEN the action is denied with 403.

## MODIFIED: Blueprint Deletion Authorization

| Req | Rule |
|-----|------|
| 2.1 | Only organization owner MAY delete or soft-delete blueprints. |
| 2.2 | Developer MUST NOT delete their own blueprints. |
| 2.3 | Maintainer MUST NOT delete blueprints. |

### Scenarios
- **Developer denied**: GIVEN a developer who created a blueprint, WHEN the developer attempts to delete it, THEN the action is denied with 403.
- **Maintainer denied**: GIVEN a maintainer, WHEN attempting to delete any blueprint, THEN the action is denied with 403.
- **Owner allowed**: GIVEN an owner, WHEN deleting any blueprint, THEN the blueprint is soft-deleted.

## MODIFIED: Invitation Acceptance Security

| Req | Rule |
|-----|------|
| 3.1 | When a user is explicitly passed to `AcceptInvitation`, their email MUST match the invitation email. |
| 3.2 | Invitation MUST be valid (not expired, not already used). |
| 3.3 | Organization member limit MUST be enforced on acceptance. |

### Scenarios
- **Email mismatch denied**: GIVEN a valid invitation for `alice@example.com`, WHEN `AcceptInvitation` is called with a user whose email is `bob@example.com`, THEN the action is denied.
- **Expired denied**: GIVEN an expired invitation, WHEN a user attempts to accept it, THEN the action is denied.
- **Valid accepted**: GIVEN a valid invitation and a user whose email matches, WHEN the user accepts, THEN the user is added to the organization.
- **Limit denied**: GIVEN an organization at its plan's `max_members_per_org`, WHEN a user attempts to accept an invitation, THEN the action is denied.

## ADDED: Duplicate Tab Type Prevention

| Req | Rule |
|-----|------|
| 4.1 | `TabManager` MUST reject adding a tab with a type that already exists. |
| 4.2 | Form submission MUST validate no duplicate tab types. |
| 4.3 | Error message MUST indicate which type is duplicated. |

### Scenarios
- **Duplicate rejected**: GIVEN a blueprint with a `config` tab, WHEN the user attempts to add another `config` tab, THEN the action is rejected with an error naming `config`.
- **Unique allowed**: GIVEN a blueprint with a `config` tab, WHEN the user attempts to add a `vars` tab, THEN the tab is added.

## ADDED: Per-User Blueprint Limit

| Req | Rule |
|-----|------|
| 5.1 | `Plan` model MUST have `max_blueprints_per_user` field. |
| 5.2 | `CreateBlueprint` MUST check the user's total blueprint count across all orgs against their plan's limit. |
| 5.3 | `RestoreBlueprint` MUST also check the per-user limit. |
| 5.4 | Default/migration value MUST be `null` (unlimited) for backward compatibility. |

### Scenarios
- **At limit denied**: GIVEN a user whose total blueprint count equals their plan's `max_blueprints_per_user`, WHEN the user attempts to create a blueprint in any org, THEN the action is denied.
- **Under limit allowed**: GIVEN a user below their plan's limit, WHEN creating a blueprint, THEN the blueprint is created.
- **Multi-org counted**: GIVEN a user in two organizations with blueprints in both, WHEN the total count reaches the per-user limit, THEN further creation in either org is denied.

## MODIFIED: Blueprint Transfer Limit Check

| Req | Rule |
|-----|------|
| 6.1 | Transfer MUST check target organization's `max_blueprints_per_org` before executing. |
| 6.2 | Transfer MUST verify the actor is owner of both source and target orgs. |
| 6.3 | Transfer MUST verify slug uniqueness in the target org. |

### Scenarios
- **Target full denied**: GIVEN a target organization at its blueprint limit, WHEN the owner attempts to transfer a blueprint into it, THEN the transfer is denied.
- **Under limit allowed**: GIVEN a target organization below its limit and a unique slug, WHEN the owner transfers a blueprint, THEN the transfer succeeds.
- **Duplicate slug denied**: GIVEN a target organization with an existing blueprint slug, WHEN the owner attempts to transfer a blueprint with the same slug, THEN the transfer is denied.

## ADDED: Email Verification, Disposable Email Blocking, MFA

| Req | Rule |
|-----|------|
| 7.1 | Registration MUST reject emails from known disposable/temporary domains. |
| 7.2 | After registration, a verification email with a signed URL MUST be sent. |
| 7.3 | User MUST verify email before accessing protected features (or after a grace period). |
| 7.4 | Verification token MUST expire within 24 hours. |
| 7.5 | MFA infrastructure: database table for email verification codes MUST exist. |
| 7.6 | MFA codes MUST be time-limited (10 minutes) and single-use. |
| 7.7 | MFA code MUST be sent via email. |

### Scenarios
- **Disposable blocked**: GIVEN a registration request with `user@tempmail.com`, WHEN validated, THEN it is rejected with a validation error.
- **Verification sent**: GIVEN a valid non-disposable email, WHEN registration completes, THEN a verification email is queued.
- **Expired token denied**: GIVEN a signed verification URL older than 24 hours, WHEN the user visits it, THEN verification is denied.
- **Valid MFA accepted**: GIVEN a user with a valid 10-minute MFA code, WHEN they submit it, THEN authentication proceeds.
- **Expired MFA denied**: GIVEN a user with an MFA code older than 10 minutes, WHEN they submit it, THEN it is rejected.
- **Reused MFA denied**: GIVEN a user who already used an MFA code, WHEN they submit the same code again, THEN it is rejected.
