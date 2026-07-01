<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Load The Auto Loader & Environment
|--------------------------------------------------------------------------
*/

require __DIR__.'/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Console Application
|--------------------------------------------------------------------------
|
| When we run the console application, the current CLI command will be
| executed in this console and the response sent back to a terminal
| or another output device for the developers.
|
| We register the custom CoVa commands on the application instance.
|
*/

$app = new App\Console\Application;

$app->add(
    $app['app']->make(App\Commands\ConfigSetKeyCommand::class)
);

$app->run();
