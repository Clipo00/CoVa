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
use App\Modules\Blueprint\Tabs\AiContext\SegmentRegistry;
use App\Modules\Blueprint\Tabs\AiContext\Skills\ApiDesignSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\CICDSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\CleanArchitectureSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\DockerSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\LaravelConventionsSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\PSR12Skill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\ReactExpertSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\SOLIDSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\StripeSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\TailwindSkill;
use App\Modules\Blueprint\Tabs\AiContext\Skills\TypeScriptStrictSkill;
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
        // Register skills registry (includes all skills: code conventions + technology skills)
        $this->app->singleton('blueprint.skills', function () {
            $registry = new SegmentRegistry;

            // Code convention skills
            $registry->register(new PSR12Skill);
            $registry->register(new SOLIDSkill);
            $registry->register(new CleanArchitectureSkill);
            $registry->register(new LaravelConventionsSkill);
            $registry->register(new TypeScriptStrictSkill);
            $registry->register(new DockerSkill);
            $registry->register(new CICDSkill);

            // Technology skills
            $registry->register(new StripeSkill);
            $registry->register(new TailwindSkill);
            $registry->register(new ReactExpertSkill);
            $registry->register(new VueExpertSkill);
            $registry->register(new ApiDesignSkill);

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
                                ['type' => 'skill', 'name' => 'psr12', 'content' => null],
                                ['type' => 'skill', 'name' => 'solid', 'content' => null],
                                ['type' => 'skill', 'name' => 'clean-architecture', 'content' => null],
                                ['type' => 'skill', 'name' => 'laravel-conventions', 'content' => null],
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
                                ['type' => 'skill', 'name' => 'typescript-strict', 'content' => null],
                                ['type' => 'skill', 'name' => 'solid', 'content' => null],
                                ['type' => 'skill', 'name' => 'clean-architecture', 'content' => null],
                                ['type' => 'skill', 'name' => 'react-expert', 'content' => null],
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
                                ['type' => 'skill', 'name' => 'typescript-strict', 'content' => null],
                                ['type' => 'skill', 'name' => 'solid', 'content' => null],
                                ['type' => 'skill', 'name' => 'clean-architecture', 'content' => null],
                                ['type' => 'skill', 'name' => 'react-expert', 'content' => null],
                                ['type' => 'skill', 'name' => 'tailwind', 'content' => null],
                            ],
                        ]],
                    ],
                ],
            ];
        });
    }
}
