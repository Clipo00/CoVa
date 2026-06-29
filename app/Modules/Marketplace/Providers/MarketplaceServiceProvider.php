<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Providers;

use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Marketplace\Livewire\MarketplaceList;
use App\Modules\Marketplace\Livewire\NotificationBell;
use App\Modules\Marketplace\Policies\MarketplacePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class MarketplaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'marketplace');

        // Register separate gates to avoid overriding BlueprintPolicy
        Gate::define('marketplace.subscribe', [MarketplacePolicy::class, 'subscribe']);
        Gate::define('marketplace.vote', [MarketplacePolicy::class, 'vote']);

        Livewire::component('marketplace.list', MarketplaceList::class);
        Livewire::component('marketplace.notification-bell', NotificationBell::class);
    }
}
