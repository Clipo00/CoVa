<?php

declare(strict_types=1);

/**
 * Standalone PHAR build script for covar CLI.
 *
 * This script bypasses the Laravel Zero Application bootstrap because:
 *   1. PHP 8.4+ made method_exists() stricter — Laravel Zero v2.0's
 *      Application.php calls method_exists(null, 'boot') when service
 *      providers return void from register(). See: cli/BUILD.md
 *   2. Custom commands (ListCommand, FetchCommand) require ApiClient
 *      with a valid config, which breaks the app:build command.
 *
 * Usage: php -d phar.readonly=0 build-phar.php
 */

define('BASE_PATH', __DIR__);

$name = 'covar';
$buildPath = BASE_PATH . '/builds';
$pharFile = $buildPath . '/' . $name . '.phar';

if (!Phar::canWrite()) {
    echo "ERROR: phar.readonly is enabled. Run with: php -d phar.readonly=0 build-phar.php\n";
    exit(1);
}

if (!is_dir($buildPath)) {
    mkdir($buildPath, 0755, true);
}

$structure = [
    'app/',
    'bootstrap/',
    'vendor/',
    'config/',
];

echo "Building: $name\n";

// Read APP_URL from parent project's .env for default base URL
$envPath = dirname(__DIR__) . '/.env';
$baseUrl = 'http://127.0.0.1:8000';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (preg_match('/^APP_URL=(.+)$/m', $envContent, $matches)) {
        $baseUrl = trim($matches[1]);
    }
}
echo "Default base URL: $baseUrl\n";

// Write the base URL into the CLI config
$configPath = BASE_PATH . '/config/config.php';
$configContent = file_get_contents($configPath);
$configContent = preg_replace(
    "/'url' => '.*?'/",
    "'url' => '{$baseUrl}'",
    $configContent
);
file_put_contents($configPath, $configContent);

// Build regex pattern matching Laravel Zero's Build.php approach
$pattern = '#(' . implode('|', $structure) . ')#';

$phar = new Phar(
    $pharFile,
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    $name
);

$phar->buildFromDirectory(BASE_PATH, $pattern);

// Add the entry point (it's at root, not inside any structure dir)
$phar->addFile(BASE_PATH . '/covar', 'covar');

$phar->setStub($phar->createDefaultStub('bootstrap/init.php'));

// Rename .phar to remove extension (Laravel Zero convention)
rename($pharFile, $buildPath . '/' . $name);

echo "Standalone application compiled into: builds/$name\n";
