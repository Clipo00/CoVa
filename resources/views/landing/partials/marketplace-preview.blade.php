<section class="py-20 sm:py-28 bg-gray-50 dark:bg-gray-900/50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section title --}}
        <div class="text-center mb-16" x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 reveal">
                {{ __('landing.marketplace_title') }}
            </h2>
        </div>

        {{-- Blueprint cards grid (mock data para la landing) --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $blueprints = [
                    [
                        'title_key' => 'blueprint_1_title',
                        'desc_key' => 'blueprint_1_desc',
                        'badge_key' => 'blueprint_1_badge',
                        'badge_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                        'downloads_key' => 'blueprint_1_downloads',
                        'icon' => 'L',
                        'icon_color' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                    ],
                    [
                        'title_key' => 'blueprint_2_title',
                        'desc_key' => 'blueprint_2_desc',
                        'badge_key' => 'blueprint_2_badge',
                        'badge_color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        'downloads_key' => 'blueprint_2_downloads',
                        'icon' => 'R',
                        'icon_color' => 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',
                    ],
                    [
                        'title_key' => 'blueprint_3_title',
                        'desc_key' => 'blueprint_3_desc',
                        'badge_key' => 'blueprint_3_badge',
                        'badge_color' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        'downloads_key' => 'blueprint_3_downloads',
                        'icon' => 'N',
                        'icon_color' => 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400',
                    ],
                    [
                        'title_key' => 'blueprint_4_title',
                        'desc_key' => 'blueprint_4_desc',
                        'badge_key' => 'blueprint_4_badge',
                        'badge_color' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        'downloads_key' => 'blueprint_4_downloads',
                        'icon' => 'P',
                        'icon_color' => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400',
                    ],
                    [
                        'title_key' => 'blueprint_5_title',
                        'desc_key' => 'blueprint_5_desc',
                        'badge_key' => 'blueprint_5_badge',
                        'badge_color' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                        'downloads_key' => 'blueprint_5_downloads',
                        'icon' => 'V',
                        'icon_color' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
                    ],
                    [
                        'title_key' => 'blueprint_6_title',
                        'desc_key' => 'blueprint_6_desc',
                        'badge_key' => 'blueprint_6_badge',
                        'badge_color' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        'downloads_key' => 'blueprint_6_downloads',
                        'icon' => 'G',
                        'icon_color' => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400',
                    ],
                ];
            @endphp

            @foreach ($blueprints as $blueprint)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200/60 dark:border-gray-700/60 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 cursor-default" x-data x-reveal>
                    {{-- Card header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl {{ $blueprint['icon_color'] }} flex items-center justify-center font-bold text-sm">
                                {{ $blueprint['icon'] }}
                            </span>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                                    {{ __("landing.{$blueprint['title_key']}") }}
                                </h3>
                                <span class="inline-block mt-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $blueprint['badge_color'] }}">
                                    {{ __("landing.{$blueprint['badge_key']}") }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 leading-relaxed">
                        {{ __("landing.{$blueprint['desc_key']}") }}
                    </p>

                    {{-- Footer stats --}}
                    <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            {{ __("landing.{$blueprint['downloads_key']}") }}
                        </span>
                        <span>{{ __('landing.badge_cova_team') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Marketplace CTA --}}
        <div class="text-center mt-12" x-data x-reveal>
            <p class="text-gray-500 dark:text-gray-400 mb-4 text-sm">
                {{ __('landing.marketplace_more') }}
            </p>
            <a href="{{ route('register') }}"
               class="inline-flex items-center px-6 py-3 text-base font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 rounded-xl transition-all">
                {{ __('landing.marketplace_cta') }}
            </a>
        </div>
    </div>
</section>
