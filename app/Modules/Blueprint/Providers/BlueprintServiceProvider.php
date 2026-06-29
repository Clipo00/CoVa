<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Providers;

use App\Modules\Blueprint\Actions\ResolveBlueprint;
use App\Modules\Blueprint\Contracts\TabInterface;
use App\Modules\Blueprint\Livewire\Forms\BlueprintCreateForm;
use App\Modules\Blueprint\Livewire\Forms\BlueprintEditForm;
use App\Modules\Blueprint\Livewire\Tables\BlueprintList;
use App\Modules\Blueprint\Livewire\Components\BlueprintPreviewPanel;
use App\Modules\Blueprint\Livewire\Components\TabManager;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Policies\BlueprintPolicy;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\AiContextTab;
use App\Modules\Blueprint\Tabs\AiContext\Presets\CleanArchitecturePreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\CICDPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\DockerPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\LaravelConventionsPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\PSR12Preset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\SOLIDPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\TypeScriptStrictPreset;
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Skills\ApiDesignSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\ReactExpertSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\StripeSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\TailwindSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\VueExpertSkill;
use App\Modules\Blueprint\Tabs\McpServersTab;
use App\Modules\Blueprint\Tabs\TabRegistry;
use App\Modules\Blueprint\Tabs\VscodeExtensionsTab;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class BlueprintServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerTabRegistries();
        $this->registerTemplates();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'blueprint');

        Gate::policy(Blueprint::class, BlueprintPolicy::class);

        Livewire::component('blueprint.forms.blueprint-create-form', BlueprintCreateForm::class);
        Livewire::component('blueprint.forms.blueprint-edit-form', BlueprintEditForm::class);
        Livewire::component('blueprint.tables.blueprint-list', BlueprintList::class);
        Livewire::component('blueprint.components.tab-manager', TabManager::class);
        Livewire::component('blueprint.components.preview-panel', BlueprintPreviewPanel::class);
    }

    /**
     * Register tab registries and singletons.
     */
    private function registerTabRegistries(): void
    {
        // Register presets registry
        $this->app->singleton('blueprint.presets', function () {
            $registry = new SegmentRegistry();
            $registry->register(new PSR12Preset());
            $registry->register(new SOLIDPreset());
            $registry->register(new CleanArchitecturePreset());
            $registry->register(new DockerPreset());
            $registry->register(new CICDPreset());
            $registry->register(new LaravelConventionsPreset());
            $registry->register(new TypeScriptStrictPreset());
            return $registry;
        });

        // Register skills registry
        $this->app->singleton('blueprint.skills', function () {
            $registry = new SegmentRegistry();
            $registry->register(new StripeSkill());
            $registry->register(new TailwindSkill());
            $registry->register(new ApiDesignSkill());
            $registry->register(new ReactExpertSkill());
            $registry->register(new VueExpertSkill());
            return $registry;
        });

        // Register agent generator
        $this->app->singleton(AgentGenerator::class, function ($app) {
            return new AgentGenerator(
                $app->make('blueprint.presets'),
                $app->make('blueprint.skills'),
            );
        });

        // Register tabs registry
        $this->app->singleton(TabRegistry::class, function ($app) {
            $registry = new TabRegistry();
            $registry->register($app->make(VscodeExtensionsTab::class));
            $registry->register($app->make(McpServersTab::class));
            $registry->register($app->make(AiContextTab::class));
            return $registry;
        });

        // Bind ResolveBlueprint action
        $this->app->bind(ResolveBlueprint::class, function ($app) {
            return new ResolveBlueprint(
                $app->make(TabRegistry::class),
            );
        });
    }

    /**
     * Register blueprint templates as a singleton.
     */
    private function registerTemplates(): void
    {
        $this->app->singleton('blueprint.templates', function () {
            return [
                '' => ['label' => 'blueprint.template_none', 'tabs' => []],
                'laravel' => [
                    'label' => 'blueprint.template_laravel',
                    'tabs' => [
                        [
                            'type' => 'vscode_extensions',
                            'config' => [
                                'extensions' => [
                                    'bmewburn.vscode-intelephense-client',
                                    'amiralizadeh9480.laravel-extra-snippets',
                                    'onecentlin.laravel-blade-snippets',
                                    'shufo.vscode-blade-formatter',
                                ],
                            ],
                        ],
                        [
                            'type' => 'mcp_servers',
                            'config' => [
                                'servers' => [
                                    [
                                        'name' => 'laravel-mcp',
                                        'command' => 'npx',
                                        'args' => ['-y', '@laravel-mcp/server'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'ai_context',
                            'config' => [
                                'presets' => ['laravel-conventions'],
                                'skills' => [],
                                'custom_rules' => '',
                            ],
                        ],
                    ],
                ],
                'nodejs' => [
                    'label' => 'blueprint.template_nodejs',
                    'tabs' => [
                        [
                            'type' => 'vscode_extensions',
                            'config' => [
                                'extensions' => [
                                    'dbaeumer.vscode-eslint',
                                    'esbenp.prettier-vscode',
                                    'christian-kohler.npm-intellisense',
                                ],
                            ],
                        ],
                        [
                            'type' => 'mcp_servers',
                            'config' => [
                                'servers' => [
                                    [
                                        'name' => 'node-mcp',
                                        'command' => 'npx',
                                        'args' => ['-y', '@modelcontextprotocol/server-filesystem'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'ai_context',
                            'config' => [
                                'presets' => ['typescript-strict'],
                                'skills' => ['react-expert'],
                                'custom_rules' => '',
                            ],
                        ],
                    ],
                ],
                'python' => [
                    'label' => 'blueprint.template_python',
                    'tabs' => [
                        [
                            'type' => 'vscode_extensions',
                            'config' => [
                                'extensions' => [
                                    'ms-python.python',
                                    'ms-python.vscode-pylance',
                                    'njpwerner.autodocstring',
                                ],
                            ],
                        ],
                        [
                            'type' => 'mcp_servers',
                            'config' => [
                                'servers' => [
                                    [
                                        'name' => 'python-mcp',
                                        'command' => 'uvx',
                                        'args' => ['@modelcontextprotocol/server-filesystem'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'ai_context',
                            'config' => [
                                'presets' => ['docker'],
                                'skills' => [],
                                'custom_rules' => '',
                            ],
                        ],
                    ],
                ],
            ];
        });
    }
}
