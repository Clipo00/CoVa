<?php

declare(strict_types=1);

use App\Modules\Marketplace\Controllers\MarketplaceController;
use Illuminate\Support\Facades\Route;

Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/{uuid}', [MarketplaceController::class, 'show'])->name('marketplace.show');

Route::middleware('auth')->group(function () {
    Route::post('/marketplace/{blueprint:uuid}/subscribe', [MarketplaceController::class, 'subscribe'])
        ->middleware('throttle:5,1')
        ->name('marketplace.subscribe');

    Route::post('/marketplace/{blueprint:uuid}/vote', [MarketplaceController::class, 'vote'])
        ->middleware('throttle:30,1')
        ->name('marketplace.vote');
});
