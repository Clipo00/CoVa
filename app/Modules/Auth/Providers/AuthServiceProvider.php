<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Modules\Auth\Livewire\Components\UserDropdown;
use App\Modules\Auth\Livewire\Forms\LoginForm;
use App\Modules\Auth\Livewire\Forms\MfaChallengeForm;
use App\Modules\Auth\Livewire\Forms\RegisterForm;
use App\Modules\Auth\Livewire\Forms\UserProfileForm;

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
        Livewire::component('auth.forms.user-profile-form', UserProfileForm::class);
        Livewire::component('auth.forms.mfa-challenge-form', MfaChallengeForm::class);
        Livewire::component('auth.components.user-dropdown', UserDropdown::class);
    }
}
