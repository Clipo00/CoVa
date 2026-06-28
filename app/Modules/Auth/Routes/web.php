<?php

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Controllers\EmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');

    // Password reset — OWASP A07: throttle to prevent enumeration & brute-force
    Route::middleware('throttle:5,1')->group(function () {
        Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])
            ->name('password.request');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])
            ->name('password.reset');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');

    // Email verification
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->name('verification.resend');

    // MFA first-login setup interstitial
    Route::get('/mfa/setup', [AuthController::class, 'showMfaSetup'])->name('mfa.setup');
});

// MFA challenge — OWASP A07: throttle to prevent brute-force (5 attempts/min)
// NOTE: must be OUTSIDE auth middleware because user is NOT logged in yet
// when redirected here from login (identified via session mfa_user_id).
Route::middleware('throttle:5,1')->group(function () {
    Route::get('/mfa/challenge', [AuthController::class, 'showMfaChallenge'])->name('mfa.challenge');
});
