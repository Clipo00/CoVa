<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Providers;

use App\Modules\Blueprint\Livewire\Forms\BlueprintCreateForm;
use App\Modules\Blueprint\Livewire\Forms\BlueprintEditForm;
use App\Modules\Blueprint\Livewire\Tables\BlueprintList;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Policies\BlueprintPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class BlueprintServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'blueprint');

        Gate::policy(Blueprint::class, BlueprintPolicy::class);

        Livewire::component('blueprint.forms.blueprint-create-form', BlueprintCreateForm::class);
        Livewire::component('blueprint.forms.blueprint-edit-form', BlueprintEditForm::class);
        Livewire::component('blueprint.tables.blueprint-list', BlueprintList::class);
    }
}
