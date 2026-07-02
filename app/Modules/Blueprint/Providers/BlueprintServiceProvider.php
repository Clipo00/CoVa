<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Providers;

use App\Modules\Blueprint\Actions\ResolveBlueprint;
use App\Modules\Blueprint\Livewire\Components\TabManager;
use App\Modules\Blueprint\Livewire\Forms\BlueprintCreateForm;
use App\Modules\Blueprint\Livewire\Forms\BlueprintEditForm;
use App\Modules\Blueprint\Livewire\Tables\BlueprintList;
use App\Modules\Blueprint\Models\Blueprint;
use App\Modules\Blueprint\Policies\BlueprintPolicy;
use App\Modules\Blueprint\Tabs\AiContext\AgentGenerator;
use App\Modules\Blueprint\Tabs\AiContext\Agents\AgentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Agents\FrontendDeveloperAgent;
use App\Modules\Blueprint\Tabs\AiContext\Agents\FullstackDeveloperAgent;
use App\Modules\Blueprint\Tabs\AiContext\Agents\LaravelDeveloperAgent;
use App\Modules\Blueprint\Tabs\AiContext\AiContextTab;
use App\Modules\Blueprint\Tabs\AiContext\Presets\CleanArchitecturePreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\LaravelConventionsPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\PSR12Preset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\SOLIDPreset;
use App\Modules\Blueprint\Tabs\AiContext\Presets\TypeScriptStrictPreset;
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Skills\ReactExpertSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\StripeSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\TailwindSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\VueExpertSkill;
use App\Modules\Blueprint\Tabs\McpServersTab;
use App\Modules\Blueprint\Tabs\ScriptsTab;
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
        $this->loadViewsFrom(__DIR__.'/../Views', 'blueprint');

        Gate::policy(Blueprint::class, BlueprintPolicy::class);

        Livewire::component('blueprint.forms.blueprint-create-form', BlueprintCreateForm::class);
        Livewire::component('blueprint.forms.blueprint-edit-form', BlueprintEditForm::class);
        Livewire::component('blueprint.tables.blueprint-list', BlueprintList::class);
        Livewire::component('blueprint.components.tab-manager', TabManager::class);
    }

    /**
     * Register tab registries and singletons.
     */
    private function registerTabRegistries(): void
    {
        // Register presets registry
        $this->app->singleton('blueprint.presets', function () {
            $registry = new SegmentRegistry;
            $registry->register(new PSR12Preset);
            $registry->register(new SOLIDPreset);
            $registry->register(new CleanArchitecturePreset);
            $registry->register(new LaravelConventionsPreset);
            $registry->register(new TypeScriptStrictPreset);

            return $registry;
        });

        // Register skills registry
        $this->app->singleton('blueprint.skills', function () {
            $registry = new SegmentRegistry;
            $registry->register(new StripeSkill);
            $registry->register(new TailwindSkill);
            $registry->register(new ReactExpertSkill);
            $registry->register(new VueExpertSkill);

            return $registry;
        });

        // Register agents registry
        $this->app->singleton('blueprint.agents', function () {
            $registry = new AgentRegistry;
            $registry->register(new LaravelDeveloperAgent);
            $registry->register(new FrontendDeveloperAgent);
            $registry->register(new FullstackDeveloperAgent);

            return $registry;
        });

        // Register agent generator
        $this->app->singleton(AgentGenerator::class, function ($app) {
            return new AgentGenerator(
                $app->make('blueprint.presets'),
                $app->make('blueprint.skills'),
                $app->make('blueprint.agents'),
            );
        });

        // Register tabs registry
        $this->app->singleton(TabRegistry::class, function ($app) {
            $registry = new TabRegistry;
            $registry->register($app->make(VscodeExtensionsTab::class));
            $registry->register($app->make(McpServersTab::class));
            $registry->register($app->make(ScriptsTab::class));
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
     * Register blueprint templates for the create form.
     */
    private function registerTemplates(): void
    {
        $this->app->singleton('blueprint.templates', function () {
            return [
                'laravel' => [
                    'label' => 'Laravel',
                    'tabs' => [
                        ['type' => 'vscode_extensions', 'config' => ['extensions' => [
                            'bmewburn.vscode-intelephense-client',
                            'amiralizadeh9480.laravel-extra-intellisense',
                            'shufo.vscode-blade-formatter',
                        ]]],
                        ['type' => 'ai_context', 'config' => [
                            'segments' => [
                                ['type' => 'preset', 'name' => 'psr12', 'content' => null],
                                ['type' => 'preset', 'name' => 'solid', 'content' => null],
                                ['type' => 'preset', 'name' => 'clean-architecture', 'content' => null],
                                ['type' => 'preset', 'name' => 'laravel-conventions', 'content' => null],
                                ['type' => 'skill', 'name' => 'stripe', 'content' => null],
                                ['type' => 'skill', 'name' => 'tailwind', 'content' => null],
                            ],
                        ]],
                    ],
                ],
                'nextjs' => [
                    'label' => 'Next.js',
                    'tabs' => [
                        ['type' => 'vscode_extensions', 'config' => ['extensions' => [
                            'dsznajder.es7-react-js-snippets',
                            'bradlc.vscode-tailwindcss',
                        ]]],
                        ['type' => 'ai_context', 'config' => [
                            'segments' => [
                                ['type' => 'preset', 'name' => 'typescript-strict', 'content' => null],
                                ['type' => 'preset', 'name' => 'solid', 'content' => null],
                                ['type' => 'preset', 'name' => 'clean-architecture', 'content' => null],
                                ['type' => 'skill', 'name' => 'react', 'content' => null],
                                ['type' => 'skill', 'name' => 'tailwind', 'content' => null],
                            ],
                        ]],
                    ],
                ],
                'remix' => [
                    'label' => 'Remix',
                    'tabs' => [
                        ['type' => 'vscode_extensions', 'config' => ['extensions' => [
                            'dsznajder.es7-react-js-snippets',
                            'bradlc.vscode-tailwindcss',
                        ]]],
                        ['type' => 'ai_context', 'config' => [
                            'segments' => [
                                ['type' => 'preset', 'name' => 'typescript-strict', 'content' => null],
                                ['type' => 'preset', 'name' => 'solid', 'content' => null],
                                ['type' => 'preset', 'name' => 'clean-architecture', 'content' => null],
                                ['type' => 'skill', 'name' => 'react', 'content' => null],
                                ['type' => 'skill', 'name' => 'tailwind', 'content' => null],
                            ],
                        ]],
                    ],
                ],
            ];
        });
    }
}
