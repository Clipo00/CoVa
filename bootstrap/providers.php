<?php

use App\Modules\Shared\Providers\ModuleServiceProvider;
use App\Modules\Shared\Providers\RouteServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    ModuleServiceProvider::class,
    RouteServiceProvider::class,
];
