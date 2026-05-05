<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Tests\Unit\Tabs;

use App\Modules\Blueprint\DTOs\TabOutput;
use App\Modules\Blueprint\Enums\TabType;
use App\Modules\Blueprint\Tabs\VscodeExtensionsTab;
use PHPUnit\Framework\TestCase;

class VscodeExtensionsTabTest extends TestCase
{
    private VscodeExtensionsTab $tab;

    protected function setUp(): void
    {
        $this->tab = new VscodeExtensionsTab();
    }

    public function test_type_returns_vscode_extensions(): void
    {
        $this->assertEquals('vscode_extensions', $this->tab->type());
    }

    public function test_generate_returns_extensions_list(): void
    {
        $config = [
            'extensions' => [
                'bradlc.vscode-tailwindcss',
                'esbenp.prettier-vscode',
            ],
        ];

        $output = $this->tab->generate($config);

        $this->assertInstanceOf(TabOutput::class, $output);
        $this->assertEquals(TabType::VSCODE_EXTENSIONS, $output->type);
        $this->assertIsArray($output->content);
        $this->assertEquals(['bradlc.vscode-tailwindcss', 'esbenp.prettier-vscode'], $output->content['extensions']);
    }

    public function test_generate_creates_install_command(): void
    {
        $config = [
            'extensions' => [
                'bradlc.vscode-tailwindcss',
            ],
        ];

        $output = $this->tab->generate($config);

        $this->assertEquals(
            'code --install-extension bradlc.vscode-tailwindcss',
            $output->content['install_command']
        );
    }

    public function test_generate_handles_empty_extensions(): void
    {
        $config = [
            'extensions' => [],
        ];

        $output = $this->tab->generate($config);

        $this->assertEquals([], $output->content['extensions']);
        $this->assertEquals('', $output->content['install_command']);
    }

    public function test_generate_ignores_invalid_extensions(): void
    {
        $config = [
            'extensions' => [
                'valid-extension',
                123,
                '',
                'another-valid',
            ],
        ];

        $output = $this->tab->generate($config);

        $this->assertEquals(['valid-extension', 'another-valid'], $output->content['extensions']);
    }
}
