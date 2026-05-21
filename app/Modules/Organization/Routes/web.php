<?php

use App\Modules\Organization\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::get('/organizations/{slug}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::get('/organizations/{slug}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::get('/organizations/{slug}/members', [OrganizationController::class, 'members'])->name('organizations.members');

    // Acciones mutantes con rate limiting (30/min para operaciones normales)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/organizations/{slug}/update', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::post('/organizations/{slug}/delete', [OrganizationController::class, 'destroy'])->name('organizations.destroy');
        Route::post('/organizations/{slug}/restore', [OrganizationController::class, 'restore'])->name('organizations.restore');
    });

    // Acciones sensibles con rate limiting más restrictivo (5/min)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/organizations/{slug}/force-delete', [OrganizationController::class, 'forceDestroy'])->name('organizations.force-destroy');
        Route::post('/organizations/{slug}/invite', [OrganizationController::class, 'invite'])->name('organizations.invite');
        Route::post('/organizations/{slug}/members/{user_id}/role', [OrganizationController::class, 'updateMemberRole'])->name('organizations.members.role');
    });

    // Store member sin throttle extra
    Route::post('/organizations/{slug}/members/store', [OrganizationController::class, 'storeMember'])->name('organizations.members.store');
});
