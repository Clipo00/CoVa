<?php

use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Route;

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