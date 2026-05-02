<?php

use App\Providers\AppServiceProvider;
use App\Modules\Shared\Providers\ModuleServiceProvider;
use App\Modules\Shared\Providers\RouteServiceProvider;

return [
    AppServiceProvider::class,
    ModuleServiceProvider::class,
    RouteServiceProvider::class,
];
