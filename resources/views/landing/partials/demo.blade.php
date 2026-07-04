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
                paused: false,
                slides: [
                    { id: 'dashboard', label: '{{ __('landing.demo_dashboard') }}' },
                    { id: 'org', label: '{{ __('landing.demo_org') }}' },
                    { id: 'blueprint', label: '{{ __('landing.demo_blueprint') }}' },
                    { id: 'tabs', label: '{{ __('landing.demo_tabs') }}' }
                ],
                start() {
                    if (this.paused) return;
                    clearInterval(this.interval);
                    this.interval = setInterval(() => {
                        this.next();
                    }, 5000);
                },
                pause() {
                    this.paused = true;
                    clearInterval(this.interval);
                },
                resume() {
                    this.paused = false;
                    this.start();
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
                    if (!this.paused) this.start();
                }
            }"
            x-init="start()"
            @mouseenter="pause()"
            @mouseleave="resume()"
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
                    <div class="flex-1 p-6 overflow-hidden">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('landing.demo_dash_title') }}</h3>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">AM</span>
                                </div>
                            </div>
                        </div>
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
                    <div class="flex-1 p-6 overflow-hidden">
                        <div class="max-w-md mx-auto">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-6">{{ __('landing.demo_org_title') }}</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('landing.demo_org_name_label') }}</label>
                                    <div class="block w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
                                        {{ __('landing.demo_org_name_placeholder') }}
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('landing.demo_org_slug_label') }}</label>
                                    <div class="block w-full px-3 py-2 bg-gray-100 dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('landing.demo_org_slug_placeholder') }}
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('landing.demo_org_slug_help') }}</p>
                                </div>
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
                                <div class="pt-4">
                                    <div class="w-full py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg text-center opacity-90">
                                        {{ __('landing.demo_org_submit') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Create Blueprint slide (single column, card-based) --}}
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
                    <div class="flex-1 p-4 overflow-y-auto space-y-3">
                        {{-- Card 1: General Information --}}
                        <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-indigo-50 dark:bg-indigo-900/20 border-b border-indigo-100 dark:border-indigo-800/30">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ __('landing.demo_bp_title') }}</span>
                            </div>
                            <div class="p-4 space-y-3">
                                {{-- Organization selector --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('landing.demo_bp_org_label') }}</label>
                                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
                                        <span class="w-5 h-5 rounded bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-[10px] font-bold text-indigo-600 dark:text-indigo-400">M</span>
                                        {{ __('landing.demo_dash_org_name') }}
                                        <svg class="w-3.5 h-3.5 ml-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </div>
                                {{-- Title + Slug --}}
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('landing.demo_bp_title_label') }}</label>
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100">
                                            {{ __('landing.demo_bp_title_placeholder') }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('landing.demo_bp_slug_label') }}</label>
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400 font-mono">
                                            laravel-inertia
                                        </div>
                                    </div>
                                </div>
                                {{-- Tags + Description --}}
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('landing.demo_tags_label') }}</label>
                                        <div class="flex flex-wrap gap-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg min-h-[38px]">
                                            <span class="px-1.5 py-0.5 bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 rounded text-[10px]">laravel</span>
                                            <span class="px-1.5 py-0.5 bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 rounded text-[10px]">react</span>
                                            <span class="px-1.5 py-0.5 bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 rounded text-[10px]">tailwind</span>
                                        </div>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('landing.demo_bp_desc_label') }}</label>
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('landing.demo_bp_desc_placeholder') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: Environment Variables --}}
                        <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-emerald-50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-800/30">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-4.25-4.25 7.07-7.07 4.25 4.25-7.07 7.07z"/></svg>
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ __('landing.demo_bp_vars_title') }}</span>
                                <span class="ml-auto text-[10px] text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/40 px-2 py-0.5 rounded-full">5</span>
                            </div>
                            <div class="p-3">
                                {{-- Simplified table --}}
                                <div class="text-xs">
                                    <div class="grid grid-cols-12 gap-2 mb-1.5 px-2 text-gray-400 dark:text-gray-500">
                                        <span class="col-span-4">KEY</span>
                                        <span class="col-span-3">GROUP</span>
                                        <span class="col-span-5">VALUE</span>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="grid grid-cols-12 gap-2 items-center px-2 py-1.5 bg-gray-50 dark:bg-gray-700/50 rounded">
                                            <span class="col-span-4 font-mono text-gray-700 dark:text-gray-300 text-[11px]">{{ __('landing.demo_bp_var_host') }}</span>
                                            <span class="col-span-3"><span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span><span class="text-gray-500 dark:text-gray-400">database</span></span></span>
                                            <span class="col-span-5 font-mono text-gray-600 dark:text-gray-400 text-[11px]">{{ __('landing.demo_bp_var_value_localhost') }}</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-2 items-center px-2 py-1.5 rounded">
                                            <span class="col-span-4 font-mono text-gray-700 dark:text-gray-300 text-[11px]">{{ __('landing.demo_bp_var_db') }}</span>
                                            <span class="col-span-3"><span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span><span class="text-gray-500 dark:text-gray-400">database</span></span></span>
                                            <span class="col-span-5 font-mono text-gray-600 dark:text-gray-400 text-[11px]">{{ __('landing.demo_bp_var_value_myapp') }}</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-2 items-center px-2 py-1.5 bg-gray-50 dark:bg-gray-700/50 rounded">
                                            <span class="col-span-4 font-mono text-gray-700 dark:text-gray-300 text-[11px]">{{ __('landing.demo_bp_var_app_name') }}</span>
                                            <span class="col-span-3"><span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="text-gray-500 dark:text-gray-400">app</span></span></span>
                                            <span class="col-span-5 font-mono text-gray-600 dark:text-gray-400 text-[11px]">{{ __('landing.demo_bp_var_value_app') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: Tabs (preview of AI Context inside) --}}
                        <div class="bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-amber-50 dark:bg-amber-900/20 border-b border-amber-100 dark:border-amber-800/30">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <span class="text-xs font-semibold text-amber-700 dark:text-amber-300">Tabs</span>
                                <span class="ml-auto text-[10px] text-gray-400">1 tab</span>
                            </div>
                            <div class="p-3">
                                {{-- AI Context tab card --}}
                                <div class="border border-indigo-200 dark:border-indigo-700 rounded-lg overflow-hidden">
                                    <div class="flex items-center gap-2 px-3 py-2 bg-indigo-50 dark:bg-indigo-900/20">
                                        <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400">IA</span>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">agent.md</span>
                                        <svg class="w-3.5 h-3.5 ml-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                    <div class="p-3">
                                        <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">{{ __('landing.demo_tabs_skills_label') }}</span>
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            <span class="px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded text-[10px]">{{ __('landing.demo_skill_psr12') }}</span>
                                            <span class="px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded text-[10px]">{{ __('landing.demo_skill_clean_arch') }}</span>
                                            <span class="px-1.5 py-0.5 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded text-[10px]">{{ __('landing.demo_ai_skill_stripe') }}</span>
                                            <span class="px-1.5 py-0.5 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded text-[10px]">{{ __('landing.demo_ai_skill_react') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Submit button --}}
                        <div class="pt-1">
                            <div class="w-full py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl text-center shadow-sm">
                                {{ __('landing.demo_bp_submit') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabs Configuration slide (replaces old AI Context) --}}
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
                    <div class="flex-1 p-4 overflow-y-auto">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-1">{{ __('landing.demo_tabs_title') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('landing.demo_tabs_subtitle') }}</p>

                        {{-- Tab cards stack --}}
                        <div class="space-y-2 mb-4">
                            {{-- VSCode Extensions tab --}}
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden opacity-50">
                                <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 dark:bg-blue-900/20">
                                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400">EXT</span>
                                    <span class="text-xs text-gray-500">.vscode/extensions.json</span>
                                    <span class="ml-auto text-[10px] text-gray-400">0 items</span>
                                </div>
                            </div>

                            {{-- MCP Servers tab --}}
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden opacity-50">
                                <div class="flex items-center gap-2 px-3 py-2 bg-purple-50 dark:bg-purple-900/20">
                                    <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400">MCP</span>
                                    <span class="text-xs text-gray-500">.vscode/mcp.json</span>
                                    <span class="ml-auto text-[10px] text-gray-400">0 servers</span>
                                </div>
                            </div>

                            {{-- AI Context tab (expanded, active) --}}
                            <div class="border-2 border-indigo-300 dark:border-indigo-700 rounded-lg overflow-hidden shadow-sm">
                                <div class="flex items-center gap-2 px-3 py-2.5 bg-indigo-50 dark:bg-indigo-900/30">
                                    <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400">IA</span>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300">agent.md</span>
                                    <span class="ml-auto text-[10px] text-indigo-500">4 skills</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-gray-800 space-y-2.5">
                                    {{-- Skills dropdown --}}
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('landing.demo_tabs_skills_label') }}</label>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded text-[11px] font-medium">
                                                {{ __('landing.demo_skill_psr12') }}
                                                <svg class="w-3 h-3 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 rounded text-[11px] font-medium">
                                                {{ __('landing.demo_skill_clean_arch') }}
                                                <svg class="w-3 h-3 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded text-[11px] font-medium">
                                                {{ __('landing.demo_ai_skill_stripe') }}
                                                <svg class="w-3 h-3 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded text-[11px] font-medium">
                                                {{ __('landing.demo_ai_skill_react') }}
                                                <svg class="w-3 h-3 cursor-pointer" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </span>
                                            <span class="px-2 py-1 border border-dashed border-gray-300 dark:border-gray-600 rounded text-[11px] text-gray-400">+</span>
                                        </div>
                                    </div>
                                    {{-- Generated content preview --}}
                                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded p-2.5">
                                        <span class="text-[10px] font-semibold text-amber-600 dark:text-amber-400"># agent.md</span>
                                        <pre class="text-[10px] text-gray-500 dark:text-gray-400 font-mono mt-1 leading-relaxed overflow-hidden">## PSR-12 Coding Standard
Follow PSR-12 coding standard for all PHP files...

## Clean Architecture
Follow Clean Architecture principles with domain layer...</pre>
                                    </div>
                                </div>
                            </div>

                            {{-- Scripts tab --}}
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden opacity-50">
                                <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 dark:bg-amber-900/20">
                                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400">SH</span>
                                    <span class="text-xs text-gray-500">post-install scripts</span>
                                    <span class="ml-auto text-[10px] text-gray-400">0 scripts</span>
                                </div>
                            </div>
                        </div>

                        {{-- Add Tab buttons row --}}
                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500 mb-2">{{ __('landing.demo_tabs_add') }}</p>
                            <div class="flex flex-wrap gap-1.5">
                                <span class="px-2.5 py-1.5 border border-blue-200 dark:border-blue-800 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-medium">{{ __('landing.demo_tabs_vscode') }}</span>
                                <span class="px-2.5 py-1.5 border border-purple-200 dark:border-purple-800 text-purple-600 dark:text-purple-400 rounded-lg text-[10px] font-medium">{{ __('landing.demo_tabs_mcp') }}</span>
                                <span class="px-2.5 py-1.5 border border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400 rounded-lg text-[10px] font-medium">{{ __('landing.demo_tabs_scripts') }}</span>
                                <span class="px-2.5 py-1.5 border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-medium bg-indigo-50 dark:bg-indigo-900/20">{{ __('landing.demo_tabs_ai') }}</span>
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
