<?php

use App\Modules\Blueprint\Controllers\BlueprintController;
use App\Modules\Blueprint\Models\Blueprint;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/blueprints', [BlueprintController::class, 'index'])->name('blueprints.index');
    Route::get('/blueprints/create', [BlueprintController::class, 'create'])->name('blueprints.create');
    Route::get('/blueprints/favorites', [BlueprintController::class, 'favorites'])->name('blueprints.favorites');
    Route::get('/blueprints/deleted', [BlueprintController::class, 'deleted'])->name('blueprints.deleted');

    // Slug-based GET routes (canonical)
    Route::get('/b/{blueprint:slug}', [BlueprintController::class, 'show'])
        ->name('blueprints.show')
        ->where('blueprint', '[a-z0-9]+(?:-[a-z0-9]+)*');
    Route::get('/b/{blueprint:slug}/edit', [BlueprintController::class, 'edit'])
        ->name('blueprints.edit')
        ->where('blueprint', '[a-z0-9]+(?:-[a-z0-9]+)*');
    Route::post('/b/{blueprint:slug}/download', [BlueprintController::class, 'download'])
        ->name('blueprints.download')
        ->middleware(['throttle:30,1', 'can:view,blueprint'])
        ->where('blueprint', '[a-z0-9]+(?:-[a-z0-9]+)*');

    // Legacy UUID redirects (301)
    Route::get('/blueprints/{uuid}', function (string $uuid) {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();

        return redirect()->route('blueprints.show', $blueprint->slug, 301);
    });
    Route::get('/blueprints/{uuid}/edit', function (string $uuid) {
        $blueprint = Blueprint::where('uuid', $uuid)->firstOrFail();

        return redirect()->route('blueprints.edit', $blueprint->slug, 301);
    });

    // Acciones mutantes con rate limiting
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/blueprints/{uuid}/transfer', [BlueprintController::class, 'transfer'])->name('blueprints.transfer');
        Route::post('/blueprints/{uuid}/delete', [BlueprintController::class, 'destroy'])->name('blueprints.destroy');
        Route::post('/blueprints/{uuid}/restore', [BlueprintController::class, 'restore'])->name('blueprints.restore');
        Route::post('/blueprints/{uuid}/publish', [BlueprintController::class, 'publish'])->name('blueprints.publish');
    });

    // Votación con rate limit más restrictivo (10/min)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/blueprints/{uuid}/vote', [BlueprintController::class, 'vote'])->name('blueprints.vote');
    });
});
