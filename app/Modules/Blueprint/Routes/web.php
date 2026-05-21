<?php

use App\Modules\Blueprint\Controllers\BlueprintController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/blueprints', [BlueprintController::class, 'index'])->name('blueprints.index');
    Route::get('/blueprints/create', [BlueprintController::class, 'create'])->name('blueprints.create');
    Route::get('/blueprints/favorites', [BlueprintController::class, 'favorites'])->name('blueprints.favorites');
    Route::get('/blueprints/deleted', [BlueprintController::class, 'deleted'])->name('blueprints.deleted');
    Route::get('/blueprints/{uuid}', [BlueprintController::class, 'show'])->name('blueprints.show');
    Route::get('/blueprints/{uuid}/edit', [BlueprintController::class, 'edit'])->name('blueprints.edit');

    // Acciones mutantes con rate limiting
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/blueprints/{uuid}/transfer', [BlueprintController::class, 'transfer'])->name('blueprints.transfer');
        Route::post('/blueprints/{uuid}/delete', [BlueprintController::class, 'destroy'])->name('blueprints.destroy');
        Route::post('/blueprints/{uuid}/restore', [BlueprintController::class, 'restore'])->name('blueprints.restore');
    });
});
