<section class="relative min-h-[calc(100vh-4rem)] flex items-center pt-16">
    {{-- Background gradient --}}
    <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/50 to-white dark:from-gray-900 dark:to-gray-950 pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            {{-- Left: Text content --}}
            <div class="space-y-8">
                {{-- !!! SEGURIDAD: {!! !!} es INTENCIONAL aquí.
                     Las traducciones hero_title y hero_subtitle contienen HTML
                     (<strong>, <code>) para dar énfasis. Como los archivos lang/
                     son controlados por el equipo (no input de usuario), es seguro.
                     Si en el futuro se agrega interpolación dinámica a estas keys,
                     MUDA a @{{ }} y mueve el HTML al template. --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight tracking-tight text-gray-900 dark:text-gray-100">
                    {!! __('landing.hero_title') !!}
                </h1>

                <p class="text-lg sm:text-xl text-gray-600 dark:text-gray-300 leading-relaxed">
                    {!! __('landing.hero_subtitle') !!}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center px-6 py-3.5 text-base font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 scale-100 hover:scale-[1.02] focus-visible:scale-[1.02] active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950">
                            {{ __('landing.cta_primary') }}
                            <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    @endif

                    <a href="#how-it-works"
                       class="inline-flex items-center justify-center px-6 py-3.5 text-base font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-950">
                        {{ __('landing.cta_secondary') }}
                        <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </a>
                </div>

                {{-- Trust indicator --}}
                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    {{ __('landing.hero_trust') }}
                </p>
            </div>

            {{-- Right: Terminal animation --}}
            <div class="w-full max-w-lg mx-auto lg:mx-0" x-data x-cloak>
                <x-animated-terminal
                    class="w-full"
                    :lines="json_encode([
                        ['text' => __('landing.terminal_cmd_fetch'), 'cls' => 'terminal-prompt'],
                        ['text' => '', 'cls' => ''],
                        ['text' => __('landing.terminal_downloading'), 'cls' => 'terminal-info'],
                        ['text' => __('landing.terminal_variables'), 'cls' => 'terminal-info'],
                        ['text' => __('landing.terminal_files'), 'cls' => 'terminal-info'],
                        ['text' => __('landing.terminal_presets'), 'cls' => 'terminal-info'],
                        ['text' => __('landing.terminal_scripts'), 'cls' => 'terminal-info'],
                        ['text' => '', 'cls' => ''],
                        ['text' => __('landing.terminal_ready'), 'cls' => 'terminal-success'],
                    ])"
                />

                {{-- Terminal caption --}}
                <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                    @if(config('marketplace.enabled'))
                        <code class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 rounded text-gray-500 dark:text-gray-400 font-mono text-xs">vault fetch cova-marketplace/laravel-inertia</code>
                        —
                    @endif
                    {{ __('landing.terminal_caption') }}
                </p>
            </div>
        </div>
    </div>
</section>
