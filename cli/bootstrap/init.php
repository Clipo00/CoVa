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
| We register the custom covar commands on the application instance.
|
*/

$debug = (bool) (getenv('COVAR_DEBUG') ?: getenv('APP_DEBUG'));

// Suppress PHP warnings/errors in production — prevent phar:// path exposure
set_error_handler(function (int $severity, string $message, string $file, int $line) use ($debug): bool {
    if ($debug) {
        fwrite(STDERR, "Warning: $message in $file:$line\n");
    }
    return true;
});

$app = new App\Console\Application;
$app->setName('covar');
$app->setVersion('1.0.0');

// Hide default Laravel Zero commands — only expose covar commands
foreach (['build', 'install', 'main'] as $name) {
    if ($cmd = $app->find($name)) {
        $cmd->setHidden(true);
    }
}

// Override default help command to show command list
$helpCommand = $app->find('help');
$helpCommand->setHelp(<<<'HELP'
The <info>covar</info> CLI helps you manage CoVaR blueprints:

  <info>covar config:set-key <key></info>   Configura y valida tu API key de CoVaR
  <info>covar vault:list</info>             Lista los blueprints que tienes disponibles
  <info>covar vault:fetch <slug></info>     Descarga y despliega un blueprint con sus archivos
  <info>covar help</info>                   Muestra la ayuda y comandos disponibles
HELP
);

$app->add(
    $app['app']->make(App\Commands\ConfigSetKeyCommand::class)
);

$app->add(
    $app['app']->make(App\Commands\ListCommand::class)
);

$app->add(
    $app['app']->make(App\Commands\FetchCommand::class)
);

try {
    $app->run();
} catch (\Throwable $e) {
    if ($debug) {
        fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    } else {
        fwrite(STDERR, "An error occurred. Set COVAR_DEBUG=1 for details.\n");
    }

    exit(1);
}
