<section id="pricing" class="py-20 sm:py-28 bg-white dark:bg-gray-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section title --}}
        <div class="text-center mb-16" x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 reveal">
                {{ __('landing.pricing_title') }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                {{ __('landing.pricing_subtitle') }}
            </p>
        </div>

        {{-- Pricing cards --}}
        <div class="grid md:grid-cols-3 gap-8 lg:gap-10" x-data x-reveal>
            @php
                $plans = [
                    [
                        'name' => 'Free',
                        'slug' => 'free',
                        'description' => __('landing.plan_free_desc'),
                        'price' => '0',
                        'price_label' => __('landing.plan_price_free'),
                        'features' => [
                            ['icon' => 'building', 'text' => __('landing.plan_orgs', ['count' => 2])],
                            ['icon' => 'document', 'text' => __('landing.plan_blueprints', ['count' => 3])],
                            ['icon' => 'users', 'text' => __('landing.plan_members', ['count' => 5])],
                            ['icon' => 'variable', 'text' => __('landing.plan_variables', ['count' => 50])],
                            ['icon' => 'check', 'text' => __('landing.plan_marketplace_browse')],
                        ],
                        'excluded' => [
                            __('landing.plan_api_access'),
                            __('landing.plan_marketplace_publish'),
                        ],
                        'cta' => __('landing.plan_cta_free'),
                        'cta_url' => route('register'),
                        'popular' => false,
                        'highlight' => false,
                    ],
                    [
                        'name' => 'Pro',
                        'slug' => 'pro',
                        'description' => __('landing.plan_pro_desc'),
                        'price' => '9.99',
                        'price_label' => __('landing.plan_price_month'),
                        'features' => [
                            ['icon' => 'building', 'text' => __('landing.plan_orgs', ['count' => 5])],
                            ['icon' => 'document', 'text' => __('landing.plan_blueprints', ['count' => 25])],
                            ['icon' => 'users', 'text' => __('landing.plan_members', ['count' => 50])],
                            ['icon' => 'variable', 'text' => __('landing.plan_variables', ['count' => 150])],
                            ['icon' => 'check', 'text' => __('landing.plan_api_access')],
                            ['icon' => 'check', 'text' => __('landing.plan_marketplace_publish')],
                            ['icon' => 'check', 'text' => __('landing.plan_priority_support')],
                        ],
                        'excluded' => [],
                        'cta' => __('landing.plan_cta_pro'),
                        'cta_url' => route('register'),
                        'popular' => true,
                        'highlight' => true,
                    ],
                    [
                        'name' => 'Enterprise',
                        'slug' => 'enterprise',
                        'description' => __('landing.plan_enterprise_desc'),
                        'price' => null,
                        'price_label' => __('landing.plan_price_custom'),
                        'features' => [
                            ['icon' => 'building', 'text' => __('landing.plan_orgs_unlimited')],
                            ['icon' => 'document', 'text' => __('landing.plan_blueprints_unlimited')],
                            ['icon' => 'users', 'text' => __('landing.plan_members_unlimited')],
                            ['icon' => 'variable', 'text' => __('landing.plan_variables_unlimited')],
                            ['icon' => 'check', 'text' => __('landing.plan_api_access')],
                            ['icon' => 'check', 'text' => __('landing.plan_marketplace_publish')],
                            ['icon' => 'check', 'text' => __('landing.plan_dedicated_support')],
                            ['icon' => 'check', 'text' => __('landing.plan_sso')],
                        ],
                        'excluded' => [],
                        'cta' => __('landing.plan_cta_enterprise'),
                        'cta_url' => 'mailto:enterprise@cova.app',
                        'popular' => false,
                        'highlight' => false,
                    ],
                ];
            @endphp

            @foreach($plans as $plan)
                <div class="relative flex flex-col {{ $plan['highlight'] ? 'md:-mt-4 md:mb-4' : '' }}">
                    @if($plan['popular'])
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-600 text-white shadow-lg">
                                {{ __('landing.plan_popular') }}
                            </span>
                        </div>
                    @endif

                    <div class="flex-1 bg-white dark:bg-gray-800 rounded-2xl border {{ $plan['highlight'] ? 'border-indigo-200 dark:border-indigo-800 shadow-xl shadow-indigo-500/10' : 'border-gray-200 dark:border-gray-700 shadow-sm' }} p-6 flex flex-col">
                        {{-- Header --}}
                        <div class="mb-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $plan['name'] }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $plan['description'] }}</p>
                        </div>

                        {{-- Price --}}
                        <div class="mb-6">
                            @if($plan['price'])
                                <div class="flex items-baseline">
                                    <span class="text-4xl font-bold text-gray-900 dark:text-gray-100">€{{ $plan['price'] }}</span>
                                    <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan['price_label'] }}</span>
                                </div>
                            @else
                                <div class="flex items-baseline">
                                    <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $plan['price_label'] }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Features --}}
                        <ul class="space-y-3 mb-8 flex-1">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $feature['text'] }}</span>
                                </li>
                            @endforeach
                            @foreach($plan['excluded'] as $excluded)
                                <li class="flex items-start gap-3 opacity-50">
                                    <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    <span class="text-sm text-gray-500 dark:text-gray-400 line-through">{{ $excluded }}</span>
                                </li>
                            @endforeach
                        </ul>

                        {{-- CTA --}}
                        <a href="{{ $plan['cta_url'] }}" 
                           class="block w-full py-3 px-4 text-center text-sm font-semibold rounded-xl transition-all {{ $plan['highlight'] 
                                ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98]' 
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            {{ $plan['cta'] }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Note --}}
        <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
            {{ __('landing.pricing_note') }}
        </p>
    </div>
</section>
