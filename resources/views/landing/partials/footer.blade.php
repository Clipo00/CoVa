<footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Brand --}}
            <div class="md:col-span-1">
                <a href="/" class="inline-flex items-center space-x-2 text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    <svg class="w-7 h-7" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect width="32" height="32" rx="8" fill="currentColor" class="text-indigo-600"/>
                        <path d="M9 16l4 4 10-10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>{{ config('app.name', 'CoVa') }}</span>
                </a>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    {{ __('landing.footer_tagline') }}
                </p>
            </div>

            {{-- Links --}}
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-4">
                    {{ __('landing.footer_product') }}
                </h4>
                <ul class="space-y-2">
                    <li>
                        <a href="#how-it-works" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.cta_secondary') }}
                        </a>
                    </li>
                    <li>
                        <a href="#pricing" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.nav_pricing') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('register') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus-visible:text-gray-900 dark:focus-visible:text-gray-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900 rounded">
                            {{ __('landing.footer_links_register') }}
                        </a>
                    </li>
                    <li>
                        <span class="text-sm text-gray-500 dark:text-gray-400 cursor-default">
                            {{ __('landing.footer_links_marketplace') }}
                        </span>
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
