<?php

use App\Modules\Organization\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

// Public invitation routes — OWASP A06: rate limited to prevent brute-force token guessing
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/invitations/{token}', [OrganizationController::class, 'showInvitation'])
        ->name('invitations.show');
});

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
    // NOTA: {user_id} usa ID auto-incremental porque el modelo User no tiene columna UUID.
    // Si en el futuro se migra a UUID, cambiar a {user_uuid} y actualizar los controladores.
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/organizations/{slug}/force-delete', [OrganizationController::class, 'forceDestroy'])->name('organizations.force-destroy');
        Route::post('/organizations/{slug}/invite', [OrganizationController::class, 'invite'])->name('organizations.invite');
        Route::post('/organizations/{slug}/members/{user_id}/role', [OrganizationController::class, 'updateMemberRole'])->name('organizations.members.role');
        Route::delete('/organizations/{slug}/members/{user_id}', [OrganizationController::class, 'removeMember'])->name('organizations.members.remove');
    });

    // Store member sin throttle extra
    Route::post('/organizations/{slug}/members/store', [OrganizationController::class, 'storeMember'])->name('organizations.members.store');

    // Invitation acceptance — CSRF protected, rate limited
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/invitations/{token}/accept', [OrganizationController::class, 'acceptInvitation'])
            ->name('invitations.accept');
    });
    // Store member con rate limiting para evitar abuso en creación de cuentas
    Route::post('/organizations/{slug}/members/store', [OrganizationController::class, 'storeMember'])
        ->middleware('throttle:10,1')
        ->name('organizations.members.store');
});
