<?php

use App\Modules\Auth\Models\User;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing Page (pública)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    $marketplaceEnabled = config('marketplace.enabled', false);
    $publicBlueprints = $marketplaceEnabled
        ? Blueprint::query()
            ->where('is_public', true)
            ->with(['organization', 'category'])
            ->latest()
            ->take(6)
            ->get()
        : collect();

    return view('landing.index', compact('publicBlueprints', 'marketplaceEnabled'));
})->name('landing');

/*
|--------------------------------------------------------------------------
| Locale Switcher (sin auth — disponible para guests en login/register)
|--------------------------------------------------------------------------
*/
Route::get('/locale/{locale}', function (string $locale) {
    if (!in_array($locale, config('app.supported_locales', ['es', 'en']), true)) {
        abort(404);
    }

    // Si está autenticado, persiste en BD como preferencia permanente
    if (auth()->check()) {
        auth()->user()->update(['locale' => $locale]);
    }

    // Prevenir open redirect: solo redirigir a URLs del mismo origen
    $back = url()->previous();
    $baseUrl = url('/');
    if (!str_starts_with($back, $baseUrl)) {
        $back = $baseUrl;
    }

    // Cookie forever (5 años) adjuntada DIRECTAMENTE a la respuesta de redirección
    return redirect()->to($back)->withCookie(cookie()->forever('locale', $locale));
})->name('locale.set');

/*
|--------------------------------------------------------------------------
| Notification Routes (global, auth required)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('readAll');
});

Route::middleware('auth')->get('/dashboard', function () {
    $user = auth()->user();
    $organizations = $user->organizations()->with('owner')->get();
    $plan = $user->plan;

    $maxOrganizations = $plan?->max_organizations_per_user;
    $canCreateMore = $maxOrganizations === null || $organizations->count() < $maxOrganizations;

    // Organizaciones eliminadas (soft deleted) del usuario
    $deletedOrganizations = $user->organizations()->onlyTrashed()->with('owner')->get();

    return view('dashboard', compact('organizations', 'canCreateMore', 'plan', 'deletedOrganizations'));
})->name('dashboard');