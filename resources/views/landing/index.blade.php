@extends('layouts.landing')

@section('title', __('landing.site_title'))

@section('content')
    <div
        class="pt-16"
        x-data="{
            activeTab: 'inicio',
            tabs: ['inicio', 'demo', 'precios', 'marketplace', 'docs'],
            init() {
                const hash = window.location.hash.replace('#', '');
                if (this.tabs.includes(hash)) {
                    this.activeTab = hash;
                }
                window.addEventListener('hashchange', () => {
                    const h = window.location.hash.replace('#', '');
                    if (this.tabs.includes(h)) this.activeTab = h;
                });
                window.addEventListener('switch-tab', (e) => {
                    if (this.tabs.includes(e.detail)) this.switchTab(e.detail);
                });
            },
            switchTab(tab) {
                this.activeTab = tab;
                window.location.hash = tab;
                this.$nextTick(() => {
                    document.getElementById('tab-' + tab)?.focus();
                });
            }
        }"
        x-cloak
    >
        {{-- Tab Navigation --}}
        <nav class="sticky top-16 z-30 bg-white/90 dark:bg-gray-950/90 backdrop-blur-md border-b border-gray-200/50 dark:border-gray-800/50" role="tablist" aria-label="{{ __('landing.tabs_nav_label') }}">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden -mb-px">
                    {{-- Inicio --}}
                    <button type="button" role="tab" id="tab-inicio"
                        aria-selected="true" aria-controls="panel-inicio" tabindex="0"
                        @click="switchTab('inicio')"
                        :class="activeTab === 'inicio'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="flex-shrink-0 px-5 py-3.5 text-sm font-medium border-b-2 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset">
                        {{ __('landing.tab_inicio') }}
                    </button>

                    {{-- Demo --}}
                    <button type="button" role="tab" id="tab-demo"
                        aria-selected="false" aria-controls="panel-demo" tabindex="-1"
                        @click="switchTab('demo')"
                        :class="activeTab === 'demo'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="flex-shrink-0 px-5 py-3.5 text-sm font-medium border-b-2 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset">
                        {{ __('landing.tab_demo') }}
                    </button>

                    {{-- Precios --}}
                    <button type="button" role="tab" id="tab-precios"
                        aria-selected="false" aria-controls="panel-precios" tabindex="-1"
                        @click="switchTab('precios')"
                        :class="activeTab === 'precios'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="flex-shrink-0 px-5 py-3.5 text-sm font-medium border-b-2 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset">
                        {{ __('landing.tab_precios') }}
                    </button>

                    {{-- Marketplace --}}
                    <button type="button" role="tab" id="tab-marketplace"
                        aria-selected="false" aria-controls="panel-marketplace" tabindex="-1"
                        @click="switchTab('marketplace')"
                        :class="activeTab === 'marketplace'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="flex-shrink-0 px-5 py-3.5 text-sm font-medium border-b-2 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset">
                        {{ __('landing.tab_marketplace') }}
                    </button>

                    {{-- Docs --}}
                    <button type="button" role="tab" id="tab-docs"
                        aria-selected="false" aria-controls="panel-docs" tabindex="-1"
                        @click="switchTab('docs')"
                        :class="activeTab === 'docs'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="flex-shrink-0 px-5 py-3.5 text-sm font-medium border-b-2 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-inset">
                        {{ __('landing.tab_docs') }}
                    </button>
                </div>
            </div>
        </nav>

        {{-- Tab: Inicio — Hero + Pain Point + How it Works --}}
        <div role="tabpanel" id="panel-inicio" aria-labelledby="tab-inicio"
            x-show="activeTab === 'inicio'"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            @include('landing.partials.hero')
            @include('landing.partials.pain-point')
            @include('landing.partials.how-it-works')
        </div>

        {{-- Tab: Demo — Carousel --}}
        <div role="tabpanel" id="panel-demo" aria-labelledby="tab-demo"
            x-show="activeTab === 'demo'"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            @include('landing.partials.demo')
        </div>

        {{-- Tab: Precios — Pricing --}}
        <div role="tabpanel" id="panel-precios" aria-labelledby="tab-precios"
            x-show="activeTab === 'precios'"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            @include('landing.partials.pricing')
        </div>

        {{-- Tab: Marketplace — Public blueprints showcase --}}
        <div role="tabpanel" id="panel-marketplace" aria-labelledby="tab-marketplace"
            x-show="activeTab === 'marketplace'"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            @include('landing.partials.marketplace-preview')
        </div>

        {{-- Tab: Docs — CLI quickstart guide --}}
        <div role="tabpanel" id="panel-docs" aria-labelledby="tab-docs"
            x-show="activeTab === 'docs'"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            @include('landing.partials.docs')
        </div>

        {{-- Final CTA (outside tabs, always visible) --}}
        @include('landing.partials.cta-final')
    </div>
@endsection
