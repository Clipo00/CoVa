<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use App\Modules\Shared\Livewire\Components\CopyToClipboard;
use App\Modules\Shared\Livewire\Components\ThemeToggle;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'shared');

        Livewire::component('shared.copy-to-clipboard', CopyToClipboard::class);
        Livewire::component('shared.theme-toggle', ThemeToggle::class);
    }
}
