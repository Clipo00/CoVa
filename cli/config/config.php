<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Application Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your covar CLI application. These values are accessible
    | via the Config repository: $this->laravel->config->get('app.name')
    |
    */

    'app' => [
        'name' => 'covar CLI',
        'version' => '1.0.0',
        'env' => 'production',
        'debug' => false,
        'url' => 'https://api.CoVaR.app',
        'timezone' => 'UTC',
    ],
];
