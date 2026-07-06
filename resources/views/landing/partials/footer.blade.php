<footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Brand --}}
            <div class="md:col-span-1">
                <a href="/" class="inline-flex items-center space-x-2 text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    <svg class="w-8 h-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <!-- Fondo redondeado azul -->
                        <rect width="32" height="32" rx="8" fill="currentColor" class="text-indigo-600"/>
                        <!-- Rueda de combinación circular principal -->
                        <circle cx="16" cy="15" r="7" stroke="white" stroke-width="1.5" fill="none"/>
                        <!-- Marcas de la rueda (principales) -->
                        <line x1="16" y1="6" x2="16" y2="8" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                        <line x1="16" y1="22" x2="16" y2="24" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                        <line x1="7" y1="15" x2="9" y2="15" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                        <line x1="23" y1="15" x2="25" y2="15" stroke="white" stroke-width="1.2" stroke-linecap="round"/>
                        <!-- Marcas diagonales -->
                        <line x1="9.6" y1="8.6" x2="11" y2="10" stroke="white" stroke-width="0.8" stroke-linecap="round"/>
                        <line x1="21" y1="10" x2="22.4" y2="8.6" stroke="white" stroke-width="0.8" stroke-linecap="round"/>
                        <line x1="9.6" y1="21.4" x2="11" y2="20" stroke="white" stroke-width="0.8" stroke-linecap="round"/>
                        <line x1="21" y1="20" x2="22.4" y2="21.4" stroke="white" stroke-width="0.8" stroke-linecap="round"/>
                        <!-- Centro de la rueda -->
                        <circle cx="16" cy="15" r="2" fill="white"/>
                        <!-- Indicador/puntero arriba -->
                        <path d="M14 7 L16 5 L18 7" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        <!-- Manija/Lock debajo -->
                        <rect x="13" y="25" width="6" height="2" rx="1" fill="white"/>
                        <circle cx="16" cy="26" r="0.8" fill="#4f46e5"/>
                    </svg>
                    <span>{{ config('app.name', 'CoVaR') }}</span>
                </a>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    {{ __('landing.footer_tagline') }}
                </p>
            </div>

            {{-- Product --}}
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('landing.footer_product') }}
                </h4>
                <ul class="space-y-2">
                    <li>
                        <a href="#demo" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.tab_demo') }}
                        </a>
                    </li>
                    <li>
                        <a href="#pricing" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.tab_precios') }}
                        </a>
                    </li>
                    <li>
                        <a href="#marketplace" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.footer_links_marketplace') }}
                            @if(!config('marketplace.enabled'))
                                <span class="text-xs text-gray-400 dark:text-gray-500">({{ __('landing.coming_soon') }})</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="#docs" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.tab_docs') }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Account --}}
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('landing.footer_account') }}
                </h4>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.footer_links_login') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('register') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.footer_links_register') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-400 dark:text-gray-500">
                {{ __('landing.footer_copyright') }}
            </p>
            <div class="flex items-center gap-4">
                <x-locale-switcher />
            </div>
        </div>
    </div>
</footer>
