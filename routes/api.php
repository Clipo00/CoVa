<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\Api\AuthApiController;
use App\Modules\Blueprint\Controllers\Api\BlueprintApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CoVa API v1 — Sanctum-authenticated endpoints
|--------------------------------------------------------------------------
|
| All endpoints require a valid Sanctum API token (Bearer auth).
| Error responses follow RFC 7807 Problem Details format.
|
| @see openspec/specs/api/spec.md
|
| PR 1: Blueprint endpoints
|   - GET  /api/blueprints         — Paginated, org-scoped, plan-gated listing
|   - GET  /api/blueprints/{slug}  — Fully resolved blueprint JSON
|
| PR 2: Auth endpoints
|   - GET  /api/me                 — User profile + organizations
|   - POST /api/fetch/{slug}/verify — Password verification for secrets
*/

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Blueprint listing (paginated, org-scoped, plan-gated via middleware)
    Route::get('/blueprints', [BlueprintApiController::class, 'index'])
        ->name('api.blueprints.index')
        ->middleware('api.access');

    // Blueprint resolution (full output via ResolveBlueprint)
    Route::get('/blueprints/{slug}', [BlueprintApiController::class, 'show'])
        ->name('api.blueprints.show')
        ->middleware('api.access');

    // Auth: user profile + accessible organizations (no plan gate)
    Route::get('/me', [AuthApiController::class, 'me'])
        ->name('api.me');

    // Auth: password-gated secret decryption (rate limited to 5/min)
    Route::post('/fetch/{slug}/verify', [AuthApiController::class, 'verifyPassword'])
        ->name('api.fetch.verify')
        ->middleware('throttle:5,1');
});
