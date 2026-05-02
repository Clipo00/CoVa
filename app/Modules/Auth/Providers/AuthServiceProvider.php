<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Modules\Auth\Livewire\Forms\LoginForm;
use App\Modules\Auth\Livewire\Forms\RegisterForm;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'auth');

        Livewire::component('auth.forms.login-form', LoginForm::class);
        Livewire::component('auth.forms.register-form', RegisterForm::class);
    }
}
