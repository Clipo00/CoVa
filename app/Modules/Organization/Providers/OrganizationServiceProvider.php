<?php

declare(strict_types=1);

namespace App\Modules\Organization\Providers;

use App\Modules\Organization\Livewire\Forms\CreateOrganizationForm;
use App\Modules\Organization\Livewire\Tables\OrganizationList;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Policies\OrganizationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class OrganizationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'organization');

        Gate::policy(Organization::class, OrganizationPolicy::class);

        Livewire::component('organization.forms.create-organization-form', CreateOrganizationForm::class);
        Livewire::component('organization.tables.organization-list', OrganizationList::class);
    }
}
