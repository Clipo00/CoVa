# Tasks: Security Validation Audit — Track B (Feature Additions)

## Review Workload Forecast

| Field | Value |
|-------|-------|
| Estimated changed lines | ~630 (total across 3 chained PRs) |
| Files affected | 30+ (11 new, 19+ modified) |
| 400-line budget risk | High |
| Chained PRs recommended | Yes |
| Delivery strategy | ask-always |
| Decision needed before apply | Yes |
| Chain strategy | feature-branch-chain |

Decision needed before apply: Yes
Chained PRs recommended: Yes
Chain strategy: feature-branch-chain
400-line budget risk: High

### Suggested Work Units

| Unit | Goal | Base | ~Lines | Notes |
|------|------|------|--------|-------|
| 1 | Disposable email + email verification | main | ~200 | Standalone; registration path only |
| 2 | MFA backend (migrations, actions, login flow) | PR 1 | ~270 | Depends on User model changes from PR 1 |
| 3 | MFA UI (controller, views, profile toggle, feature tests) | PR 2 | ~160 | Depends on actions from PR 2 |

## Phase 1: Disposable Email + Email Verification (PR 1 → main)

- [ ] 1.1 Create `config/disposable-email.php` with blocked domain list array
- [ ] 1.2 Create `app/Rules/DisposableEmail.php` implementing `ValidationRule`
- [ ] 1.3 Add `new DisposableEmail` rule to `RegisterRequest::rules()` and `RegisterUserData` validator
- [ ] 1.4 Implement `MustVerifyEmail` contract on `User` model (trait + interface)
- [ ] 1.5 Modify `RegisterUser::execute()` to call `$user->sendEmailVerificationNotification()` after create
- [ ] 1.6 Create `EmailVerificationController` with `verify(Request)` using `hasValidSignature()` + expiry check
- [ ] 1.7 Add `/email/verify/{id}/{hash}` signed route and verified middleware guard
- [ ] 1.8 Add `disposable_email`, `verification_sent`, `verification_*` keys to `lang/{es,en}/auth.php`
- [ ] 1.9 Create `Tests/Unit/Rules/DisposableEmailTest` and update `RegisterUserTest` to assert notification dispatched
- [ ] 1.10 Create `Tests/Feature/EmailVerificationTest` covering valid, expired, and tampered signed URLs

## Phase 2: MFA Backend (PR 2 → PR 1 branch)

- [ ] 2.1 Create migration for `mfa_codes` table (user_id FK, code, expires_at timestamp, used_at nullable)
- [ ] 2.2 Create migration to add `mfa_enabled` boolean to users table (default false)
- [ ] 2.3 Add `mfa_enabled` to User `$fillable`/`$casts` and `mfaCodes()` hasMany relation
- [ ] 2.4 Create `SendMfaCode` action: generate 6-digit code, persist to mfa_codes, queue `MfaCodeNotification`
- [ ] 2.5 Create `VerifyMfaCode` action: validate code is unused and not expired; mark `used_at`
- [ ] 2.6 Create `MfaRequiredException` (signals MFA step needed; stores user_id for session)
- [ ] 2.7 Create `MfaCodeNotification` mail notification with the 6-digit code
- [ ] 2.8 Modify `LoginUser::execute()`: use `Auth::validate()`, check `mfa_enabled`, throw `MfaRequiredException` storing user_id in session
- [ ] 2.9 Modify `LoginForm::submit()` to catch `MfaRequiredException` and redirect to `/mfa/challenge`
- [ ] 2.10 Create `Tests/Unit/Actions/SendMfaCodeTest`, `VerifyMfaCodeTest`; update `LoginUserTest` for MFA flow

## Phase 3: MFA UI + Integration (PR 3 → PR 2 branch)

- [ ] 3.1 Create `MfaChallengeForm` Livewire component (code input, submit calls VerifyMfaCode then Auth::login)
- [ ] 3.2 Create `auth::mfa-challenge` Blade view with code input and error display
- [ ] 3.3 Add `showMfaChallenge()` to `AuthController` rendering the challenge view
- [ ] 3.4 Add `/mfa/challenge` GET route (auth middleware) and wire `MfaChallengeForm`
- [ ] 3.5 Add `mfa_enabled` toggle to `UserProfileForm` (requires current password; sends test code on enable)
- [ ] 3.6 Register `MfaChallengeForm` in `AuthServiceProvider`
- [ ] 3.7 Add `mfa_*` translation keys to `lang/{es,en}/auth.php`
- [ ] 3.8 Create `Tests/Feature/MfaChallengeTest` covering valid, expired, and reused code scenarios
