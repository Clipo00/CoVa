<?php

use App\Modules\Organization\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::get('/organizations/{slug}', [OrganizationController::class, 'show'])->name('organizations.show');
    Route::get('/organizations/{slug}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::post('/organizations/{slug}/update', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::get('/organizations/{slug}/members', [OrganizationController::class, 'members'])->name('organizations.members');
    Route::post('/organizations/{slug}/invite', [OrganizationController::class, 'invite'])->name('organizations.invite');
});
