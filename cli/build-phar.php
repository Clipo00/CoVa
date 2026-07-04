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

// Build regex pattern matching Laravel Zero's Build.php approach
$pattern = '#(' . implode('|', $structure) . ')#';

$phar = new Phar(
    $pharFile,
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    $name
);

$phar->buildFromDirectory(BASE_PATH, $pattern);

// Add the entry point (it's at root, not inside any structure dir)
$phar->addFile(BASE_PATH . '/cova', 'covar');

$phar->setStub($phar->createDefaultStub('bootstrap/init.php'));

// Rename .phar to remove extension (Laravel Zero convention)
rename($pharFile, $buildPath . '/' . $name);

echo "Standalone application compiled into: builds/$name\n";
