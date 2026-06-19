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

- [x] 1.1 Create `config/disposable-email.php` with blocked domain list array
- [x] 1.2 Create `app/Rules/DisposableEmail.php` implementing `ValidationRule`
- [x] 1.3 Add `new DisposableEmail` rule to `RegisterRequest::rules()` and `RegisterUserData` validator
- [x] 1.4 Implement `MustVerifyEmail` contract on `User` model (trait + interface)
- [x] 1.5 Modify `RegisterUser::execute()` to fire `Registered` event (triggers verification notification sent via listener)
- [x] 1.6 Create `EmailVerificationController` with `verify(Request)` using `hasValidSignature()` + hash check
- [x] 1.7 Add `/email/verify/{id}/{hash}` route (no signed middleware — controller handles validation with redirect) and `/email/verification-notification` route
- [x] 1.8 Add `disposable_email`, `verification_sent`, `verification_success`, `verification_failed`, `verification_already_verified` keys to `lang/{es,en}/auth.php`
- [x] 1.9 Create `Tests/Unit/Rules/DisposableEmailTest` (5 test cases) and update `RegisterUserTest` (added disposable rejection + notification assertion)
- [x] 1.10 Create `Tests/Feature/EmailVerificationTest` covering valid signed URL verification, expired URL, tampered hash, already verified redirect, and resend

## Phase 2: MFA Backend (PR 2 → PR 1 branch)

- [x] 2.1 Create migration for `mfa_codes` table (user_id FK, code, expires_at, used_at nullable) with index on (user_id, code)
- [x] 2.2 Create migration to add `mfa_enabled` boolean to users table (default false, after locale)
- [x] 2.3 Add `mfa_enabled` to User `$fillable`/`$casts` and `mfaCodes()` hasMany relation; create `MfaCode` model
- [x] 2.4 Create `SendMfaCode` action: generate 6-digit code (random_int 100000-999999), persist to mfa_codes, send `MfaCodeNotification`
- [x] 2.5 Create `VerifyMfaCode` action: find latest code by user+code, check isValid(), mark used_at; returns bool
- [x] 2.6 Create `MfaRequiredException` (extends RuntimeException, carries User reference)
- [x] 2.7 Create `MfaCodeNotification` mail notification with the 6-digit code (localized via i18n keys)
- [x] 2.8 Modify `LoginUser::execute()`: use `Auth::validate()` for credential check, check `mfa_enabled`, throw `MfaRequiredException` with user
- [x] 2.9 Modify `LoginForm::submit()`: catch `MfaRequiredException`, store `mfa_user_id` in session, redirect to `route('mfa.challenge')`
- [x] 2.10 Create `SendMfaCodeTest` (3 tests), `VerifyMfaCodeTest` (5 tests); update `LoginUserTest` (2 new MFA flow tests)

## Phase 3: MFA UI + Integration (PR 3 → PR 2 branch)

- [x] 3.1 Create `MfaChallengeForm` Livewire component (code input, submit calls VerifyMfaCode then Auth::login, resend action)
- [x] 3.2 Create `auth::livewire.forms.mfa-challenge-form` Blade view with centered code input, error display, and resend button
- [x] 3.3 Add `showMfaChallenge()` to `AuthController` rendering the challenge view (placeholder from PR 2, finalized)
- [x] 3.4 Add `/mfa/challenge` GET route (auth middleware) wiring `MfaChallengeForm` (placeholder from PR 2, finalized)
- [x] 3.5 Add `mfa_enabled` toggle to `UserProfileForm` (toggle checkbox in blade, update user on submit; sends test code on enable)
- [x] 3.6 Register `MfaChallengeForm` in `AuthServiceProvider`
- [x] 3.7 Add `mfa_*` translation keys to `lang/{es,en}/auth.php` (17 keys including email notification, challenge page, code labels, errors)
- [x] 3.8 Create `Tests/Feature/MfaChallengeTest` (5 tests: page renders, valid code, invalid code, expired code, resend) and `ProfileMfaTest` (4 tests: toggle default, toggle shows state, enable, disable)
