<?php

use App\Modules\Blueprint\Controllers\BlueprintController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/blueprints', [BlueprintController::class, 'index'])->name('blueprints.index');
    Route::get('/blueprints/create', [BlueprintController::class, 'create'])->name('blueprints.create');
    Route::get('/blueprints/favorites', [BlueprintController::class, 'favorites'])->name('blueprints.favorites');
    Route::get('/blueprints/{uuid}', [BlueprintController::class, 'show'])->name('blueprints.show');
    Route::get('/blueprints/{uuid}/edit', [BlueprintController::class, 'edit'])->name('blueprints.edit');
});
