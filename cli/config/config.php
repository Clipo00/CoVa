<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Application Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your CoVa CLI application. These values are accessible
    | via the Config repository: $this->laravel->config->get('app.name')
    |
    */

    'app' => [
        'name' => 'CoVa CLI',
        'version' => '1.0.0',
        'env' => 'production',
        'debug' => false,
        'url' => 'https://api.cova.app',
        'timezone' => 'UTC',
    ],
];
