# Building the covar CLI PHAR

This document explains how to build the standalone PHAR binary for the covar CLI tool.

## Prerequisites

- PHP 8.3+ with the `phar` extension enabled
- Composer dependencies installed (`php composer install` from `cli/`)
- The build was tested on PHP 8.4.19 on Windows

## Quick Build

```bash
cd cli/

# Ensure dependencies are installed
composer install --no-dev --optimize-autoloader

# Build the PHAR using the standalone build script
php -d phar.readonly=0 build-phar.php
```

The compiled binary will be at `cli/builds/covar`.

## Smoke Test

After building, verify the PHAR works:

```bash
# Set up a test API key (required for all commands)
php builds/covar config:set-key YOUR_API_KEY --base-url=https://api.CoVaR.app

# Verify the binary shows help
php builds/covar help

# List available commands
php builds/covar vault:list
```

## PHAR Size Target

The PHAR should be under 15 MB. Current build is ~11.5 MB.

## Known Issues

### PHP 8.4 Compatibility

Laravel Zero v2.0 has a known incompatibility with PHP 8.4: the `method_exists()` function is now strict about its first argument and throws a `TypeError` when `null` is passed. This happens because some service providers' `register()` methods return `void` (null).

**Vendor patch required** (`vendor/laravel-zero/laravel-zero/app/Console/Application.php`):

```php
// Line 268 — change from:
if (method_exists($instance, 'boot')) {
// To:
if ($instance !== null && method_exists($instance, 'boot')) {
```

This patch is safe and forwards-compatible. The build script `build-phar.php` includes this in the PHAR so end users are not affected after distribution.

### Eager Command Instantiation

All custom commands (`ConfigSetKeyCommand`, `ListCommand`, `FetchCommand`) are eagerly instantiated in `bootstrap/init.php`. This means they require a valid config file (`~/.config/covar/config.json`) before any command can run, including `help`. If you get a "Config file not found" error, run `config:set-key` first.

### `phar.readonly`

PHP must have `phar.readonly=Off` to create PHARs. Override with the `-d phar.readonly=0` flag.

## Build Script

The `build-phar.php` script at the project root of `cli/` replicates the Laravel Zero `app:build` command behavior without requiring the Laravel Zero Application to bootstrap. It:

1. Creates a `Phar` instance
2. Adds files from `app/`, `bootstrap/`, `vendor/`, and `config/` directories
3. Adds the `covar` entry point
4. Sets the stub to `bootstrap/init.php`
5. Renames `.phar` to remove the extension

## CI Integration

For CI builds, add this step:

```yaml
- name: Build PHAR
  working-directory: cli
  run: |
    composer install --no-dev --optimize-autoloader
    php -d phar.readonly=0 build-phar.php
php builds/covar help
```

## Release Checklist

- [ ] `composer install --no-dev --optimize-autoloader` runs without errors
- [ ] PHAR builds successfully (under 15 MB)
- [ ] Smoke test: `php builds/covar help` shows command list
- [ ] Tests pass: `php cli/vendor/bin/phpunit -c cli/phpunit.xml`
