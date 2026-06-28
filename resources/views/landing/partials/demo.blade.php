<section id="demo" class="py-20 sm:py-28 bg-gray-50 dark:bg-gray-900/50 overflow-hidden">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section title --}}
        <div class="text-center mb-12" x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 reveal">
                {{ __('landing.demo_title') }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                {{ __('landing.demo_subtitle') }}
            </p>
        </div>

        {{-- Carousel --}}
        <div
            class="relative"
            x-data="{
                current: 0,
                interval: null,
                slides: [
                    { id: 'dashboard', label: '{{ __('landing.demo_dashboard') }}' },
                    { id: 'org', label: '{{ __('landing.demo_org') }}' },
                    { id: 'blueprint', label: '{{ __('landing.demo_blueprint') }}' },
                    { id: 'ai-context', label: '{{ __('landing.demo_ai_context') }}' }
                ],
                start() {
                    this.interval = setInterval(() => {
                        this.next();
                    }, 4000);
                },
                next() {
                    this.current = (this.current + 1) % this.slides.length;
                },
                prev() {
                    this.current = (this.current - 1 + this.slides.length) % this.slides.length;
                },
                go(index) {
                    clearInterval(this.interval);
                    this.current = index;
                    this.start();
                }
            }"
            x-init="start()"
        >
            {{-- Slides container --}}
            <div class="relative aspect-[16/10] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Dashboard slide --}}
                <div
                    x-show="current === 0"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8"
                    class="absolute inset-0 flex flex-col"
                >
                    {{-- Browser chrome --}}
                    <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="bg-gray-200 dark:bg-gray-600 rounded px-3 py-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                                cova.app/dashboard
                            </div>
                        </div>
                    </div>
                    {{-- Dashboard content --}}
                    <div class="flex-1 p-6 overflow-hidden">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('landing.demo_dash_title') }}</h3>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">AM</span>
                                </div>
                            </div>
                        </div>
                        {{-- Stats row --}}
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">3</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_dash_orgs') }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">12</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_dash_blueprints') }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">5</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_dash_vars') }}</p>
                            </div>
                        </div>
                        {{-- Org cards --}}
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-sm font-bold text-indigo-600 dark:text-indigo-400">M</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('landing.demo_dash_org_name') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_dash_org_blueprints', ['count' => 8]) }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">{{ __('landing.demo_plan_pro') }}</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center text-sm font-bold text-emerald-600 dark:text-emerald-400">P</div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('landing.demo_dash_personal') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_dash_org_blueprints', ['count' => 4]) }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300">{{ __('landing.demo_plan_free') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Create Organization slide --}}
                <div
                    x-show="current === 1"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8"
                    class="absolute inset-0 flex flex-col"
                >
                    {{-- Browser chrome --}}
                    <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="bg-gray-200 dark:bg-gray-600 rounded px-3 py-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                                cova.app/organizations/create
                            </div>
                        </div>
                    </div>
                    {{-- Form content --}}
                    <div class="flex-1 p-6 overflow-hidden">
                        <div class="max-w-md mx-auto">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">{{ __('landing.demo_org_title') }}</h3>

                            <div class="space-y-4">
                                {{-- Name field --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('landing.demo_org_name_label') }}</label>
                                    <div class="block w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
                                        {{ __('landing.demo_org_name_placeholder') }}
                                    </div>
                                </div>

                                {{-- Slug field --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('landing.demo_org_slug_label') }}</label>
                                    <div class="block w-full px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('landing.demo_org_slug_placeholder') }}
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_org_slug_help') }}</p>
                                </div>

                                {{-- Plan selector --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('landing.demo_org_plan_label') }}</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-700 border-2 border-indigo-500 rounded-lg">
                                            <div class="w-5 h-5 rounded-full border-2 border-indigo-500 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 rounded-full bg-indigo-500"></div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('landing.demo_plan_pro') }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_org_plan_pro_desc') }}</p>
                                            </div>
                                            <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">{{ __('landing.demo_org_plan_pro_price') }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg opacity-60">
                                            <div class="w-5 h-5 rounded-full border-2 border-gray-300"></div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('landing.demo_plan_free') }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_org_plan_free_desc') }}</p>
                                            </div>
                                            <span class="text-xs font-medium text-gray-500">{{ __('landing.demo_org_plan_free_price') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit button --}}
                                <div class="pt-4">
                                    <div class="w-full py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg text-center opacity-90">
                                        {{ __('landing.demo_org_submit') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- AI Context slide --}}
                <div
                    x-show="current === 3"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8"
                    class="absolute inset-0 flex flex-col"
                >
                    {{-- Browser chrome --}}
                    <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="bg-gray-200 dark:bg-gray-600 rounded px-3 py-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                                cova.app/blueprints/configure
                            </div>
                        </div>
                    </div>
                    {{-- AI Context content --}}
                    <div class="flex-1 p-6 overflow-hidden">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">{{ __('landing.demo_ai_title') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('landing.demo_ai_desc') }}</p>
                        <div class="grid grid-cols-2 gap-4 h-[calc(100%-5rem)]">
                            {{-- Left: Presets --}}
                            <div class="space-y-2">
                                <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">{{ __('landing.demo_ai_presets') }}</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-300 dark:ring-indigo-700">{{ __('landing.demo_ai_preset_psr12') }}</span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ __('landing.demo_ai_preset_solid') }}</span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-300 dark:ring-indigo-700">{{ __('landing.demo_ai_preset_clean') }}</span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ __('landing.demo_ai_preset_laravel') }}</span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ __('landing.demo_ai_preset_ts') }}</span>
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ __('landing.demo_ai_count') }}</p>
                            </div>
                            {{-- Right: Skills --}}
                            <div class="space-y-2">
                                <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">{{ __('landing.demo_ai_skills') }}</h4>
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-300 dark:ring-indigo-700">{{ __('landing.demo_ai_skill_stripe') }}</span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ __('landing.demo_ai_skill_tailwind') }}</span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-300 dark:ring-indigo-700">{{ __('landing.demo_ai_skill_react') }}</span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ __('landing.demo_ai_skill_vue') }}</span>
                                </div>
                                {{-- Output preview --}}
                                <div class="mt-4 bg-gray-50 dark:bg-gray-700/50 rounded-md p-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-1.5 py-0.5 text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 rounded">agent.md</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_ai_output') }}</span>
                                    </div>
                                    <pre class="text-xs text-gray-600 dark:text-gray-400 font-mono leading-relaxed overflow-hidden"># Agent Context

## PSR-12 Coding Standard
Follow PSR-12 coding standard...

## Clean Architecture
Follow Clean Architecture principles...

---

## Stripe Integration
When integrating Stripe payments...

---

## React Expert
Follow React best practices...</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Create Blueprint slide --}}
                <div
                    x-show="current === 2"
                    x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-8"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 -translate-x-8"
                    class="absolute inset-0 flex flex-col"
                >
                    {{-- Browser chrome --}}
                    <div class="flex items-center gap-2 px-4 py-3 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="bg-gray-200 dark:bg-gray-600 rounded px-3 py-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                                cova.app/blueprints/create
                            </div>
                        </div>
                    </div>
                    {{-- Blueprint form content --}}
                    <div class="flex-1 p-6 overflow-hidden">
                        <div class="grid grid-cols-2 gap-6 h-full">
                            {{-- Left: Form --}}
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('landing.demo_bp_title_label') }}</label>
                                    <div class="block w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
                                        {{ __('landing.demo_bp_title_placeholder') }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('landing.demo_bp_desc_label') }}</label>
                                    <div class="block w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('landing.demo_bp_desc_placeholder') }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('landing.demo_bp_cat_label') }}</label>
                                    <div class="block w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('landing.demo_bp_cat_placeholder') }}
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <div class="w-full py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg text-center opacity-90">
                                        {{ __('landing.demo_bp_submit') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Variables preview --}}
                            <div class="border-l border-gray-200 dark:border-gray-600 pl-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('landing.demo_bp_vars_title') }}</h4>
                                
                                {{-- Group: .env --}}
                                <div class="mb-3">
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 font-mono">{{ __('landing.demo_bp_file_env') }}</span>
                                    </div>
                                    <div class="space-y-1 pl-3.5 border-l-2 border-emerald-200 dark:border-emerald-800/50">
                                        <div class="flex items-center gap-2 py-1">
                                            <span class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ __('landing.demo_bp_var_host') }}</span>
                                            <span class="text-xs text-gray-500">=</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ __('landing.demo_bp_var_value_localhost') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 py-1">
                                            <span class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ __('landing.demo_bp_var_db') }}</span>
                                            <span class="text-xs text-gray-500">=</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ __('landing.demo_bp_var_value_myapp') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Group: .env.testing --}}
                                <div class="mb-3">
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 font-mono">{{ __('landing.demo_bp_file_testing') }}</span>
                                    </div>
                                    <div class="space-y-1 pl-3.5 border-l-2 border-blue-200 dark:border-blue-800/50">
                                        <div class="flex items-center gap-2 py-1">
                                            <span class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ __('landing.demo_bp_var_db') }}</span>
                                            <span class="text-xs text-gray-500">=</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ __('landing.demo_bp_var_value_test') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Group: config/app.php --}}
                                <div class="mb-3">
                                    <div class="flex items-center gap-1.5 mb-1.5">
                                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 font-mono">{{ __('landing.demo_bp_file_config') }}</span>
                                    </div>
                                    <div class="space-y-1 pl-3.5 border-l-2 border-amber-200 dark:border-amber-800/50">
                                        <div class="flex items-center gap-2 py-1">
                                            <span class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ __('landing.demo_bp_var_app_name') }}</span>
                                            <span class="text-xs text-gray-500">=</span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ __('landing.demo_bp_var_value_app') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 py-1">
                                            <span class="text-xs font-mono text-gray-700 dark:text-gray-300">{{ __('landing.demo_bp_var_app_key') }}</span>
                                            <span class="text-xs text-gray-500">=</span>
                                            <span class="text-xs text-gray-500">{{ __('landing.demo_bp_var_value_hidden') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_bp_vars_count') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation dots --}}
            <div class="flex justify-center items-center gap-3 mt-6">
                <template x-for="(slide, index) in slides" :key="slide.id">
                    <button
                        type="button"
                        @click="go(index)"
                        class="transition-all duration-300 focus:outline-none"
                        :aria-label="slide.label"
                    >
                        <div
                            class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                            :class="current === index ? 'bg-indigo-600 dark:bg-indigo-400 w-8' : 'bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500'"
                        ></div>
                    </button>
                </template>
            </div>

            {{-- Navigation arrows --}}
            <button
                type="button"
                @click="prev()"
                class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                aria-label="{{ __('landing.demo_prev') }}"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button
                type="button"
                @click="next()"
                class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 w-10 h-10 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                aria-label="{{ __('landing.demo_next') }}"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</section>
