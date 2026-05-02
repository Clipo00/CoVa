<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $modules = config('modules.enabled', []);

        foreach ($modules as $module) {
            if ($module === 'Shared') {
                continue;
            }

            $providerClass = "App\\Modules\\{$module}\\Providers\\{$module}ServiceProvider";

            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
