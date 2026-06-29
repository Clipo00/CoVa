<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Tabs;

use App\Modules\Blueprint\DTOs\ScriptEntry;
use App\Modules\Blueprint\DTOs\ScriptsConfig;
use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Tabs\ScriptsTab;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ScriptsTabTest extends TestCase
{
    private ScriptsTab $tab;

    protected function setUp(): void
    {
        $this->tab = new ScriptsTab();
    }

    // --- ScriptsTab ---

    public function test_type_returns_scripts(): void
    {
        $this->assertEquals('scripts', $this->tab->type());
    }

    public function test_generate_returns_tab_output(): void
    {
        $config = [
            'scripts' => [
                ['command' => 'composer install', 'description' => 'Install PHP dependencies', 'order' => 0],
            ],
        ];

        $output = $this->tab->generate($config);

        $this->assertInstanceOf(TabOutput::class, $output);
        $this->assertEquals(TabType::SCRIPTS, $output->type);
        $this->assertIsArray($output->content);
        $this->assertArrayHasKey('scripts', $output->content);
        $this->assertArrayHasKey('shell_script', $output->content);
    }

    public function test_generate_handles_multiple_scripts(): void
    {
        $config = [
            'scripts' => [
                ['command' => 'composer install', 'description' => 'Install PHP dependencies'],
                ['command' => 'php artisan migrate', 'description' => 'Run database migrations'],
            ],
        ];

        $output = $this->tab->generate($config);

        $this->assertCount(2, $output->content['scripts']);
    }

    public function test_generate_handles_empty_scripts(): void
    {
        $config = ['scripts' => []];

        $output = $this->tab->generate($config);

        $this->assertSame([], $output->content['scripts']);
    }

    public function test_generate_produces_shell_script_content(): void
    {
        $config = [
            'scripts' => [
                ['command' => 'composer install', 'description' => 'Install PHP dependencies'],
                ['command' => 'php artisan migrate', 'description' => 'Run database migrations'],
            ],
        ];

        $output = $this->tab->generate($config);

        $this->assertStringContainsString('#!/bin/bash', $output->content['shell_script']);
        $this->assertStringContainsString('composer install', $output->content['shell_script']);
        $this->assertStringContainsString('php artisan migrate', $output->content['shell_script']);
        $this->assertStringContainsString('Install PHP dependencies', $output->content['shell_script']);
    }

    // --- ScriptEntry validation ---

    public function test_script_entry_rejects_empty_command(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command is required');

        ScriptEntry::fromArray(['command' => '', 'description' => 'test']);
    }

    public function test_script_entry_rejects_missing_command(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command is required');

        ScriptEntry::fromArray(['description' => 'test']);
    }

    public function test_script_entry_accepts_valid_data(): void
    {
        $entry = ScriptEntry::fromArray([
            'command' => 'composer install',
            'description' => 'Install PHP dependencies',
            'order' => 0,
        ]);

        $this->assertSame('composer install', $entry->command);
        $this->assertSame('Install PHP dependencies', $entry->description);
        $this->assertSame(0, $entry->order);
    }

    public function test_script_entry_defaults_description_to_empty(): void
    {
        $entry = ScriptEntry::fromArray(['command' => 'composer install']);

        $this->assertSame('', $entry->description);
        $this->assertSame(0, $entry->order);
    }

    public function test_script_entry_defaults_order_to_zero(): void
    {
        $entry = ScriptEntry::fromArray(['command' => 'composer install']);

        $this->assertSame(0, $entry->order);
    }

    public function test_script_entry_to_array(): void
    {
        $entry = new ScriptEntry('composer install', 'Install deps', 1);

        $data = $entry->toArray();

        $this->assertSame([
            'command' => 'composer install',
            'description' => 'Install deps',
            'order' => 1,
        ], $data);
    }

    // --- ScriptsConfig ---

    public function test_scripts_config_from_array(): void
    {
        $config = ScriptsConfig::fromArray([
            'scripts' => [
                ['command' => 'composer install', 'description' => 'Install deps'],
            ],
        ]);

        $this->assertCount(1, $config->scripts);
        $this->assertInstanceOf(ScriptEntry::class, $config->scripts[0]);
        $this->assertSame('composer install', $config->scripts[0]->command);
    }

    public function test_scripts_config_from_empty_array(): void
    {
        $config = ScriptsConfig::fromArray([]);

        $this->assertSame([], $config->scripts);
    }

    public function test_scripts_config_from_non_array_scripts(): void
    {
        $config = ScriptsConfig::fromArray(['scripts' => 'invalid']);

        $this->assertSame([], $config->scripts);
    }

    public function test_scripts_config_to_array(): void
    {
        $config = ScriptsConfig::fromArray([
            'scripts' => [
                ['command' => 'composer install', 'description' => 'Install deps', 'order' => 0],
            ],
        ]);

        $result = $config->toArray();

        $this->assertArrayHasKey('scripts', $result);
        $this->assertCount(1, $result['scripts']);
        $this->assertSame('composer install', $result['scripts'][0]['command']);
    }

    public function test_scripts_config_has_scripts(): void
    {
        $config = ScriptsConfig::fromArray([
            'scripts' => [
                ['command' => 'composer install'],
            ],
        ]);

        $this->assertTrue($config->hasScripts());
    }

    public function test_scripts_config_has_no_scripts_when_empty(): void
    {
        $config = ScriptsConfig::fromArray([]);

        $this->assertFalse($config->hasScripts());
    }

    public function test_scripts_config_to_shell_script(): void
    {
        $config = ScriptsConfig::fromArray([
            'scripts' => [
                ['command' => 'composer install', 'description' => 'Install PHP deps'],
                ['command' => 'php artisan migrate', 'description' => 'Run migrations'],
            ],
        ]);

        $script = $config->toShellScript();

        $this->assertStringContainsString('#!/bin/bash', $script);
        $this->assertStringContainsString('# Install PHP deps', $script);
        $this->assertStringContainsString('composer install', $script);
        $this->assertStringContainsString('# Run migrations', $script);
        $this->assertStringContainsString('php artisan migrate', $script);
    }

    public function test_scripts_config_to_shell_script_empty(): void
    {
        $config = ScriptsConfig::fromArray([]);

        $this->assertSame('', $config->toShellScript());
    }

    public function test_scripts_config_to_output_array(): void
    {
        $config = ScriptsConfig::fromArray([
            'scripts' => [
                ['command' => 'composer install', 'description' => 'Install deps', 'order' => 0],
            ],
        ]);

        $output = $config->toOutputArray();

        $this->assertArrayHasKey('scripts', $output);
        $this->assertArrayHasKey('shell_script', $output);
        $this->assertCount(1, $output['scripts']);
    }

    // --- No execution (OWASP A05) ---

    public function test_no_execution_for_dangerous_commands(): void
    {
        $config = [
            'scripts' => [
                ['command' => 'rm -rf /', 'description' => 'Dangerous command'],
            ],
        ];

        // Should NOT throw, should NOT execute, just generate
        $output = $this->tab->generate($config);

        $this->assertStringContainsString('rm -rf /', $output->content['shell_script']);
    }
}
