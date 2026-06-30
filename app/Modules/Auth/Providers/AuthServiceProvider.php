<?php

declare(strict_types=1);

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Livewire\Components\UserDropdown;
use App\Modules\Auth\Livewire\Forms\ForgotPasswordForm;
use App\Modules\Auth\Livewire\Forms\LoginForm;
use App\Modules\Auth\Livewire\Forms\MfaChallengeForm;
use App\Modules\Auth\Livewire\Forms\MfaSetupForm;
use App\Modules\Auth\Livewire\Forms\OnboardingWizard;
use App\Modules\Auth\Livewire\Forms\RegisterForm;
use App\Modules\Auth\Livewire\Forms\ResetPasswordForm;
use App\Modules\Auth\Livewire\Forms\UserProfileForm;
use App\Modules\Auth\Livewire\ApiTokenManager;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'auth');

        Livewire::component('auth.forms.login-form', LoginForm::class);
        Livewire::component('auth.forms.forgot-password-form', ForgotPasswordForm::class);
        Livewire::component('auth.forms.reset-password-form', ResetPasswordForm::class);
        Livewire::component('auth.forms.register-form', RegisterForm::class);
        Livewire::component('auth.forms.user-profile-form', UserProfileForm::class);
        Livewire::component('auth.forms.mfa-challenge-form', MfaChallengeForm::class);
        Livewire::component('auth.forms.mfa-setup-form', MfaSetupForm::class);
        Livewire::component('auth.forms.onboarding-wizard', OnboardingWizard::class);
        Livewire::component('auth.components.user-dropdown', UserDropdown::class);
        Livewire::component('auth.api-token-manager', ApiTokenManager::class);
    }
}
