<section id="how-it-works" class="py-20 sm:py-28 bg-white dark:bg-gray-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section title --}}
        <div class="text-center mb-16" x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 reveal">
                {{ __('landing.how_title') }}
            </h2>
        </div>

        {{-- Steps --}}
        <div class="grid md:grid-cols-3 gap-8 lg:gap-12 relative">
            {{-- Connector line (desktop) --}}
            <div class="hidden md:block absolute top-16 left-[calc(16.67%+2rem)] right-[calc(16.67%+2rem)] h-0.5 bg-gradient-to-r from-indigo-200 via-indigo-400 to-indigo-200 dark:from-indigo-800 dark:via-indigo-600 dark:to-indigo-800" aria-hidden="true"></div>

            {{-- Step 1: Define --}}
            <div class="relative flex flex-col items-center text-center" x-data x-reveal>
                <div class="w-16 h-16 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-6 relative z-10 ring-4 ring-white dark:ring-gray-950 shadow-lg shadow-indigo-500/10">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                    </svg>
                </div>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-600 text-white text-sm font-bold mb-4 relative z-10 ring-4 ring-white dark:ring-gray-950">
                    1
                </span>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('landing.step1_title') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed max-w-sm">
                    {{ __('landing.step1_desc') }}
                </p>
            </div>

            {{-- Step 2: Publish --}}
            <div class="relative flex flex-col items-center text-center" x-data x-reveal>
                <div class="w-16 h-16 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mb-6 relative z-10 ring-4 ring-white dark:ring-gray-950 shadow-lg shadow-indigo-500/10">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-600 text-white text-sm font-bold mb-4 relative z-10 ring-4 ring-white dark:ring-gray-950">
                    2
                </span>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('landing.step2_title') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed max-w-sm">
                    {{ __('landing.step2_desc') }}
                </p>
            </div>

            {{-- Step 3: Fetch --}}
            <div class="relative flex flex-col items-center text-center" x-data x-reveal>
                <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mb-6 relative z-10 ring-4 ring-white dark:ring-gray-950 shadow-lg shadow-emerald-500/10">
                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-600 text-white text-sm font-bold mb-4 relative z-10 ring-4 ring-white dark:ring-gray-950">
                    3
                </span>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('landing.step3_title') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed max-w-sm">
                    {{ __('landing.step3_desc') }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                    {{ __('landing.step3_note') }}
                </p>
            </div>
        </div>
    </div>
</section>
