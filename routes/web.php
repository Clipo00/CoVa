<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->get('/dashboard', function () {
    return view('layouts.app', ['title' => 'Dashboard']);
})->name('dashboard');
