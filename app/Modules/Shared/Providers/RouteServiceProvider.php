<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        $modules = config('modules.enabled', []);

        foreach ($modules as $module) {
            $routesPath = app_path("Modules/{$module}/Routes/web.php");

            if (file_exists($routesPath)) {
                Route::middleware('web')
                    ->namespace("App\\Modules\\{$module}\\Controllers")
                    ->group($routesPath);
            }

            $viewsPath = app_path("Modules/{$module}/Views");

            if (is_dir($viewsPath)) {
                $viewNamespace = strtolower($module);
                view()->addLocation($viewsPath);
                // También registramos como namespace para @module('auth::login')
                // Pero por ahora usamos la ruta directa
            }
        }
    }
}
