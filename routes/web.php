<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get('/dashboard', function () {
    $user = auth()->user();
    $organizations = $user->organizations()->with('owner')->get();
    $plan = $user->plan;
    
    $maxOrganizations = $plan?->max_organizations_per_user;
    $canCreateMore = $maxOrganizations === null || $organizations->count() < $maxOrganizations;
    
    return view('dashboard', compact('organizations', 'canCreateMore', 'plan'));
})->name('dashboard');
