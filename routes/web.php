<?php

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

/*
|--------------------------------------------------------------------------
| Onboarding Wizard (auth required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/onboarding', function () {
    $user = auth()->user();
    if ($user->onboarding_completed_at !== null) {
        return redirect()->route('dashboard');
    }

    return view('auth::onboarding');
})->name('onboarding');

/*
|--------------------------------------------------------------------------
| Pricing Page (auth required — accessible to all authenticated users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->get('/pricing', function () {
    $plans = \App\Modules\Shared\Models\Plan::where('is_active', true)
        ->orderBy('price_monthly')
        ->get();

    return view('pricing', compact('plans'));
})->name('pricing');

Route::middleware(['auth', 'onboarding'])->get('/dashboard', function () {
    $user = auth()->user();
    $organizations = $user->organizations()->with('owner')->withCount(['blueprints', 'members'])->get();
    $plan = $user->plan;

    $maxOrganizations = $plan?->max_organizations_per_user;
    $canCreateMore = $maxOrganizations === null || $organizations->count() < $maxOrganizations;

    // Organizaciones eliminadas (soft deleted) del usuario
    $deletedOrganizations = $user->organizations()->onlyTrashed()->with('owner')->get();

    // Stats row aggregates
    $totalOrgs = $organizations->count();
    $totalBlueprints = $organizations->sum('blueprints_count');
    $favoritesCount = $user->favoriteBlueprints()->count();

    return view('dashboard', compact(
        'organizations',
        'canCreateMore',
        'plan',
        'deletedOrganizations',
        'totalOrgs',
        'totalBlueprints',
        'favoritesCount'
    ));
})->name('dashboard');
