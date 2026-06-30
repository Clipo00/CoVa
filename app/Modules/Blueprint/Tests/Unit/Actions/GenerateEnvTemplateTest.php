<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Actions;

use App\Modules\Blueprint\Actions\GenerateEnvTemplate;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Models\BlueprintVariable;
use PHPUnit\Framework\TestCase;

class GenerateEnvTemplateTest extends TestCase
{
    private GenerateEnvTemplate $action;

    protected function setUp(): void
    {
        $this->action = new GenerateEnvTemplate;
    }

    public function test_generates_key_equals_value_format(): void
    {
        $blueprint = $this->createBlueprintWithVariables([
            $this->makeVariable(['key' => 'APP_NAME', 'default_value' => 'MyApp', 'is_secret' => false]),
            $this->makeVariable(['key' => 'DB_HOST', 'default_value' => 'localhost', 'is_secret' => false]),
        ]);

        $result = $this->action->execute($blueprint);

        $this->assertStringContainsString('APP_NAME=MyApp', $result);
        $this->assertStringContainsString('DB_HOST=localhost', $result);
    }

    public function test_secret_variables_have_empty_values(): void
    {
        $blueprint = $this->createBlueprintWithVariables([
            $this->makeVariable(['key' => 'API_KEY', 'default_value' => 'super-secret', 'is_secret' => true]),
            $this->makeVariable(['key' => 'DB_PASSWORD', 'default_value' => 'p@ssw0rd', 'is_secret' => true]),
        ]);

        $result = $this->action->execute($blueprint);

        $this->assertStringContainsString('API_KEY=', $result);
        $this->assertStringContainsString('DB_PASSWORD=', $result);
        $this->assertStringNotContainsString('super-secret', $result);
        $this->assertStringNotContainsString('p@ssw0rd', $result);
    }

    public function test_handles_empty_variables_collection(): void
    {
        $blueprint = $this->createBlueprintWithVariables([]);

        $result = $this->action->execute($blueprint);

        $this->assertSame('', $result);
    }

    public function test_groups_variables_by_section(): void
    {
        $blueprint = $this->createBlueprintWithVariables([
            $this->makeVariable(['key' => 'APP_NAME', 'default_value' => 'MyApp', 'section' => 'app', 'is_secret' => false]),
            $this->makeVariable(['key' => 'APP_DEBUG', 'default_value' => 'true', 'section' => 'app', 'is_secret' => false]),
            $this->makeVariable(['key' => 'DB_HOST', 'default_value' => 'localhost', 'section' => 'database', 'is_secret' => false]),
            $this->makeVariable(['key' => 'DB_PORT', 'default_value' => '3306', 'section' => 'database', 'is_secret' => false]),
        ]);

        $result = $this->action->execute($blueprint);

        $this->assertStringContainsString('# --- app ---', $result);
        $this->assertStringContainsString('# --- database ---', $result);
        // app vars come before database vars
        $appPos = strpos($result, '# --- app ---');
        $dbPos = strpos($result, '# --- database ---');
        $this->assertNotFalse($appPos);
        $this->assertNotFalse($dbPos);
        $this->assertLessThan($dbPos, $appPos);
    }

    public function test_omits_variable_with_null_key(): void
    {
        $blueprint = $this->createBlueprintWithVariables([
            $this->makeVariable(['key' => '', 'default_value' => 'nokey', 'is_secret' => false]),
            $this->makeVariable(['key' => 'VALID_KEY', 'default_value' => 'value', 'is_secret' => false]),
        ]);

        $result = $this->action->execute($blueprint);

        $this->assertStringNotContainsString('nokey', $result);
        $this->assertStringContainsString('VALID_KEY=value', $result);
    }

    private function makeVariable(array $attributes): BlueprintVariable
    {
        $variable = $this->createMock(BlueprintVariable::class);
        $variable->method('__get')->willReturnCallback(function (string $name) use ($attributes) {
            return $attributes[$name] ?? null;
        });
        $variable->method('__isset')->willReturnCallback(function (string $name) use ($attributes) {
            return isset($attributes[$name]);
        });

        return $variable;
    }

    private function createBlueprintWithVariables(array $variables): Blueprint
    {
        $blueprint = $this->createMock(Blueprint::class);
        $blueprint->method('__get')->willReturnCallback(function (string $name) use ($variables) {
            if ($name === 'variables') {
                return collect($variables);
            }

            return null;
        });
        $blueprint->method('__isset')->willReturnCallback(function (string $name) {
            return $name === 'variables';
        });

        return $blueprint;
    }
}
