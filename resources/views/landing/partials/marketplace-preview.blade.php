<section id="marketplace" class="py-20 sm:py-28 bg-gray-50 dark:bg-gray-900/50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section title --}}
        <div class="text-center mb-16" x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 reveal">
                {{ __('landing.marketplace_title') }}
            </h2>
        </div>

        @if($marketplaceEnabled && $publicBlueprints->isNotEmpty())
            {{-- Real blueprint cards from DB --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($publicBlueprints as $blueprint)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200/60 dark:border-gray-700/60 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 focus:outline-none" tabindex="0" role="article" x-data x-reveal>
                        {{-- Card header --}}
                        <div class="flex items-start gap-3 mb-4">
                            <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center font-bold text-sm text-indigo-600 dark:text-indigo-400" aria-hidden="true">
                                {{ strtoupper(mb_substr($blueprint->title, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm truncate">
                                    {{ $blueprint->title }}
                                </h3>
                                @if($blueprint->category)
                                    <span class="inline-block mt-1 text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $blueprint->category->name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Description --}}
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed line-clamp-3">
                            {{ $blueprint->description ?: __('landing.marketplace_no_desc') }}
                        </p>

                        {{-- Footer: organization name --}}
                        @if($blueprint->organization)
                            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $blueprint->organization->name }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Marketplace CTA --}}
            <div class="text-center mt-12" x-data x-reveal>
                <p class="text-gray-600 dark:text-gray-400 mb-4 text-sm">
                    {{ __('landing.marketplace_more') }}
                </p>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center px-6 py-3 text-base font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 rounded-xl transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                    {{ __('landing.marketplace_cta') }}
                </a>
            </div>
        @else
            {{-- Empty state --}}
            <div class="max-w-lg mx-auto text-center py-12" x-data x-reveal>
                <div class="relative inline-flex items-center justify-center w-20 h-20 rounded-full bg-indigo-100 dark:bg-indigo-900/30 mb-5">
                    <svg class="w-10 h-10 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('landing.marketplace_empty_title') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                    {{ __('landing.marketplace_empty') }}
                </p>
            </div>
        @endif
    </div>
</section>
