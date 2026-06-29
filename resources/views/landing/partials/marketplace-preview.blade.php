<section class="py-20 sm:py-28 bg-gray-50 dark:bg-gray-900/50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section title --}}
        <div class="text-center mb-16" x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 reveal">
                {{ __('landing.marketplace_title') }}
            </h2>
        </div>

        {{-- Blueprint cards grid (real data from DB) --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($publicBlueprints ?? [] as $blueprint)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200/60 dark:border-gray-700/60 hover:shadow-lg hover:-translate-y-1 focus-visible:shadow-lg focus-visible:-translate-y-1 transition-all duration-200 cursor-default focus:outline-none" tabindex="0" role="article" x-data x-reveal>
                    {{-- Card header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm" aria-hidden="true">
                                {{ strtoupper(substr($blueprint->title, 0, 1)) }}
                            </span>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                                    {{ $blueprint->title }}
                                </h3>
                                @if($blueprint->relationLoaded('category') && $blueprint->category)
                                    <span class="inline-block mt-1 text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                        {{ $blueprint->category->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 leading-relaxed">
                        {{ \Illuminate\Support\Str::limit($blueprint->description ?? '', 100) }}
                    </p>

                    {{-- Footer stats --}}
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                        <span>{{ $blueprint->organization->name ?? 'CoVa' }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                    <p>{{ __('landing.marketplace_empty') }}</p>
                </div>
            @endforelse
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
    </div>
</section>
