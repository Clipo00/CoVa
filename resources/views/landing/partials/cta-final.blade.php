<section class="py-20 sm:py-28 bg-gradient-to-br from-indigo-600 to-indigo-800 dark:from-indigo-700 dark:to-indigo-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                {{ __('landing.cta_final_title') }}
            </h2>

            <p class="text-lg text-indigo-100 mb-8 leading-relaxed">
                {{ __('landing.cta_final_subtitle') }}
            </p>

            @if (Route::has('register'))
                <a href="{{ route('register') }}"
                   class="inline-flex items-center px-8 py-4 text-lg font-semibold text-indigo-700 bg-white hover:bg-indigo-50 rounded-xl transition-all shadow-xl shadow-indigo-900/25 hover:shadow-indigo-900/40 scale-100 hover:scale-[1.03] focus-visible:scale-[1.03] active:scale-[0.97] focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-indigo-700">
                    {{ __('landing.cta_final_button') }}
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            @endif

            <p class="mt-4 text-sm text-indigo-200">
                {{ __('landing.cta_final_note') }}
            </p>
        </div>
    </div>
</section>
