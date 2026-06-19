# Design: Security Validation Audit — Track B

## Technical Approach

Add per-user blueprint limit enforcement in Actions and UI pre-checks. Implement email verification with Laravel's `MustVerifyEmail`, block disposable domains via a custom validation rule, and build email-based MFA as an opt-in two-step login flow.

## Architecture Decisions

| Decision | Choice | Alternatives | Rationale |
|---|---|---|---|
| Per-user limit exception | Reuse `MaxBlueprintsReachedException` with parameterized message | New exception class | Keeps UI error handling consistent; existing catch blocks in controllers/forms continue to work |
| Disposable email list | `config/disposable-email.php` with domain array | `.env` string or Composer package | Version-controlled, testable, no external dependency |
| Email verification | Built-in `MustVerifyEmail` + `VerifyEmail` notification | Custom token table + mailer | Signed URLs, queue-ready, battle-tested expiry logic |
| MFA login flow | `Auth::validate()` → check MFA → throw `MfaRequiredException` | Partial auth token | Minimal change to `LoginUser`; `LoginForm` catches exception and redirects to challenge |
| RestoreBlueprint user param | Add `User $user` to `execute()` signature | Use `auth()->user()` inside Action | Follows Action skill convention: explicit params over global auth state |

## Data Flow

```
CreateBlueprint
  → plan = organization->plan
  → userBlueprints = Blueprint::where('created_by', auth()->id())->count()
  → if max_blueprints_per_user !== null && userBlueprints >= limit: throw
  → proceed with existing per-org check

Login
  → Auth::validate(credentials)
  → if user.mfa_enabled: SendMfaCode, session('mfa_user_id'), throw MfaRequiredException
  → else Auth::login() → redirect dashboard

MfaChallenge
  → submit code → VerifyMfaCode → if valid: Auth::login() → redirect dashboard
```

## File Changes

| File | Action | Description |
|---|---|---|
| `database/migrations/2026_06_19_add_max_blueprints_per_user_to_plans.php` | Create | Nullable integer, default null |
| `database/migrations/2026_06_19_create_mfa_codes_table.php` | Create | user_id, code, expires_at, used_at |
| `database/migrations/2026_06_19_add_mfa_enabled_to_users.php` | Create | Boolean default false |
| `app/Modules/Shared/Models/Plan.php` | Modify | Add `max_blueprints_per_user` to `$fillable` / `$casts` |
| `app/Modules/Blueprint/Actions/CreateBlueprint.php` | Modify | Add per-user active blueprint count check after per-org check |
| `app/Modules/Blueprint/Actions/RestoreBlueprint.php` | Modify | Add `User $user` param; add per-user limit check |
| `app/Modules/Blueprint/Controllers/BlueprintController.php` | Modify | Include per-user limit in `hasAvailableSlots` logic; pass user to RestoreBlueprint |
| `app/Modules/Auth/Models/User.php` | Modify | Implement `MustVerifyEmail`; add `mfa_enabled` fillable/cast; add `blueprints()` hasMany |
| `app/Modules/Auth/Actions/RegisterUser.php` | Modify | Call `$user->sendEmailVerificationNotification()` after create |
| `app/Modules/Auth/Actions/LoginUser.php` | Modify | Use `Auth::validate()`, check `mfa_enabled`, throw `MfaRequiredException` |
| `app/Modules/Auth/Requests/RegisterRequest.php` | Modify | Add `new DisposableEmail` to email rule |
| `app/Modules/Auth/DTOs/RegisterUserData.php` | Modify | Add disposable email validation to internal Validator |
| `config/disposable-email.php` | Create | Array of blocked domains |
| `app/Rules/DisposableEmail.php` | Create | Custom `ValidationRule`; checks domain against config list |
| `app/Modules/Auth/Controllers/EmailVerificationController.php` | Create | `verify(Request $request)` with `hasValidSignature()` check; mark `email_verified_at` |
| `app/Modules/Auth/Actions/SendMfaCode.php` | Create | Generate 6-digit code, persist to `mfa_codes`, queue email notification |
| `app/Modules/Auth/Actions/VerifyMfaCode.php` | Create | Validate code against `mfa_codes` (not used, not expired); mark `used_at` |
| `app/Modules/Auth/Exceptions/MfaRequiredException.php` | Create | Signals MFA step is needed; caught by `LoginForm` |
| `app/Modules/Auth/Livewire/Forms/MfaChallengeForm.php` | Create | Enter and submit MFA code; calls `VerifyMfaCode` then `Auth::login()` |
| `app/Modules/Auth/Livewire/Forms/UserProfileForm.php` | Modify | Add `mfa_enabled` toggle; on enable, trigger `SendMfaCode` to confirm email delivery |
| `app/Modules/Auth/Notifications/MfaCodeNotification.php` | Create | Simple mail notification with the 6-digit code |
| `app/Modules/Auth/Routes/web.php` | Modify | Add `/email/verify/{id}/{hash}` (signed) and `/mfa/challenge` routes |
| `lang/*/blueprint.php`, `lang/*/auth.php` | Modify | Add per-user limit, MFA, and verification messages |
| `app/Modules/Blueprint/Tests/Unit/Actions/CreateBlueprintTest.php` | Modify | Cover per-user limit across multiple orgs |
| `app/Modules/Blueprint/Tests/Unit/Actions/RestoreBlueprintTest.php` | Modify | Pass user explicitly; cover per-user limit blocking restore |
| `app/Modules/Auth/Tests/Unit/Actions/RegisterUserTest.php` | Modify | Assert verification notification dispatched |
| `app/Modules/Auth/Tests/Unit/Actions/LoginUserTest.php` | Modify | Assert MFA redirect when enabled; assert direct login when disabled |
| `app/Modules/Auth/Tests/Feature/EmailVerificationTest.php` | Create | End-to-end signed URL verification flow |
| `app/Modules/Auth/Tests/Feature/MfaChallengeTest.php` | Create | Simulate login with MFA enabled; valid/invalid/expired code scenarios |

## Interfaces / Contracts

```php
// DisposableEmail — non-obvious because it reads from config
class DisposableEmail implements ValidationRule
{
    public function validate(string $attr, mixed $value, Closure $fail): void
    {
        $domain = substr((string)$value, strrpos((string)$value, '@') + 1);
        if (in_array($domain, config('disposable-email.domains'), true)) {
            $fail(__('auth.disposable_email'));
        }
    }
}

// RestoreBlueprint signature change
public function execute(Blueprint $blueprint, User $user): void
```

## Testing Strategy

| Layer | What | Approach |
|---|---|---|
| Unit | `CreateBlueprintTest` | Seed plan with `max_blueprints_per_user=2`, create blueprints across 2 orgs, assert 3rd throws |
| Unit | `RestoreBlueprintTest` | Pass user explicitly; assert per-user limit blocks restore |
| Unit | `DisposableEmailTest` | Instantiate rule, assert passes/fails for known domains |
| Unit | `RegisterUserTest` | Assert `sendEmailVerificationNotification` dispatched after create |
| Unit | `SendMfaCodeTest` | Assert code stored in `mfa_codes`, mail queued |
| Unit | `VerifyMfaCodeTest` | Assert valid code returns true and marks `used_at`; expired/reused return false |
| Feature | `EmailVerificationTest` | Visit signed URL, assert `email_verified_at` populated |
| Feature | `MfaChallengeTest` | Simulate login with MFA user, submit valid/invalid codes, assert session state |

## Migration / Rollout

- `max_blueprints_per_user` defaults to `null` (unlimited) — backward compatible.
- Email verification sends immediately on registration; middleware enforcement on protected routes is deferred to a follow-up to avoid blocking existing unverified users.
- MFA is strictly opt-in via profile settings; no forced enablement.

## Open Questions

- [ ] Should `BlueprintController::create()` show a global "per-user limit reached" banner when all orgs are disabled?
- [ ] Is the disposable domain list maintained manually, or should we integrate a package like `dg/rss-php`?
- [ ] Should verified-email middleware block login entirely, or only specific features (e.g., blueprint creation)?
