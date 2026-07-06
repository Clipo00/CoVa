<section class="py-20 sm:py-28 bg-gray-50 dark:bg-gray-900/50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section title --}}
        <div class="text-center mb-16" x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 reveal">
                {{ __('landing.pain_title') }}
            </h2>
        </div>

        {{-- Cards grid --}}
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Card 1: .env chaos --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200/60 dark:border-gray-700/60 hover:shadow-lg hover:-translate-y-1 focus-visible:shadow-lg focus-visible:-translate-y-1 transition-all duration-200 focus:outline-none" tabindex="0" role="article" x-data x-reveal>
                <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('landing.pain_env_title') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ __('landing.pain_env_desc') }}
                </p>
            </div>

            {{-- Card 2: Config from scratch --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200/60 dark:border-gray-700/60 hover:shadow-lg hover:-translate-y-1 focus-visible:shadow-lg focus-visible:-translate-y-1 transition-all duration-200 focus:outline-none" tabindex="0" role="article" x-data x-reveal>
                <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('landing.pain_config_title') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ __('landing.pain_config_desc') }}
                </p>
            </div>

            {{-- Card 3: No standardization --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-sm border border-gray-200/60 dark:border-gray-700/60 hover:shadow-lg hover:-translate-y-1 focus-visible:shadow-lg focus-visible:-translate-y-1 transition-all duration-200 focus:outline-none" tabindex="0" role="article" x-data x-reveal>
                <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('landing.pain_standards_title') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    {{ __('landing.pain_standards_desc') }}
                </p>
            </div>
        </div>
    </div>
</section>
