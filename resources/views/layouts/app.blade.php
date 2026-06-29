<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%234f46e5'/%3E%3Ccircle cx='16' cy='15' r='7' stroke='white' stroke-width='1.5' fill='none'/%3E%3Cline x1='16' y1='6' x2='16' y2='8' stroke='white' stroke-width='1.2' stroke-linecap='round'/%3E%3Cline x1='16' y1='22' x2='16' y2='24' stroke='white' stroke-width='1.2' stroke-linecap='round'/%3E%3Cline x1='7' y1='15' x2='9' y2='15' stroke='white' stroke-width='1.2' stroke-linecap='round'/%3E%3Cline x1='23' y1='15' x2='25' y2='15' stroke='white' stroke-width='1.2' stroke-linecap='round'/%3E%3Cline x1='9.6' y1='8.6' x2='11' y2='10' stroke='white' stroke-width='0.8' stroke-linecap='round'/%3E%3Cline x1='21' y1='10' x2='22.4' y2='8.6' stroke='white' stroke-width='0.8' stroke-linecap='round'/%3E%3Cline x1='9.6' y1='21.4' x2='11' y2='20' stroke='white' stroke-width='0.8' stroke-linecap='round'/%3E%3Cline x1='21' y1='20' x2='22.4' y2='21.4' stroke='white' stroke-width='0.8' stroke-linecap='round'/%3E%3Ccircle cx='16' cy='15' r='2' fill='white'/%3E%3Cpath d='M14 7 L16 5 L18 7' stroke='white' stroke-width='1.2' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3Crect x='13' y='25' width='6' height='2' rx='1' fill='white'/%3E%3Ccircle cx='16' cy='26' r='0.8' fill='%234f46e5'/%3E%3C/svg%3E">
    <link rel="apple-touch-icon" sizes="180x180" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'%3E%3Crect width='180' height='180' rx='32' fill='%234f46e5'/%3E%3Ccircle cx='90' cy='85' r='42' stroke='white' stroke-width='8' fill='none'/%3E%3Cline x1='90' y1='35' x2='90' y2='48' stroke='white' stroke-width='7' stroke-linecap='round'/%3E%3Cline x1='90' y1='122' x2='90' y2='135' stroke='white' stroke-width='7' stroke-linecap='round'/%3E%3Cline x1='40' y1='85' x2='53' y2='85' stroke='white' stroke-width='7' stroke-linecap='round'/%3E%3Cline x1='127' y1='85' x2='140' y2='85' stroke='white' stroke-width='7' stroke-linecap='round'/%3E%3Ccircle cx='90' cy='85' r='12' fill='white'/%3E%3Cpath d='M75 40 L90 20 L105 40' stroke='white' stroke-width='7' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3Crect x='72' y='145' width='36' height='12' rx='6' fill='white'/%3E%3C/svg%3E">

    <title>{{ config('app.name', 'CoVa') }} - @yield('title', __('layouts.site_title'))</title>

    <!-- Theme Anti-Flash -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        /* Alpine.js: hide elements until initialized */
        [x-cloak] { display: none !important; }

        /* Toast animations */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast-enter { animation: slideIn 0.3s ease-out; }
        .toast-exit { animation: slideOut 0.3s ease-in; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <div class="flex items-center sm:hidden">
                            <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" aria-label="{{ __('layouts.nav_toggle') }}" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                <span class="sr-only">{{ __('layouts.nav_toggle') }}</span>
                                <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>

                        <a href="{{ route('dashboard') }}" class="ml-4 sm:ml-0 text-xl font-bold text-gray-800 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors duration-200">
                            {{ config('app.name', 'CoVa') }}
                        </a>

                        @auth
                            <div class="hidden sm:ml-8 sm:flex sm:space-x-6">
                                <a href="{{ route('dashboard') }}" class="text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} transition-colors duration-200">
                                    {{ __('layouts.dashboard') }}
                                </a>
                                <a href="{{ route('organizations.index') }}" class="text-sm font-medium {{ request()->routeIs('organizations.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} transition-colors duration-200">
                                    {{ __('layouts.organizations') }}
                                </a>
                                <a href="{{ route('blueprints.index') }}" class="text-sm font-medium {{ request()->routeIs('blueprints.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} transition-colors duration-200">
                                    {{ __('layouts.blueprints') }}
                                </a>
                                @if(config('marketplace.enabled', false))
                                <a href="{{ route('marketplace.index') }}" class="text-sm font-medium {{ request()->routeIs('marketplace.*') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} transition-colors duration-200">
                                    {{ __('layouts.marketplace') }}
                                </a>
                                @endif
                                <a href="{{ route('blueprints.deleted') }}" class="text-sm font-medium {{ request()->routeIs('blueprints.deleted') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} transition-colors duration-200">
                                    {{ __('layouts.deleted') }}
                                </a>
                            </div>
                        @endauth
                    </div>
                    <div class="flex items-center space-x-4">
                        <livewire:shared.theme-toggle />
                        <x-locale-switcher />
                        @auth
                            @if(config('marketplace.enabled', false))
                                <livewire:marketplace.notification-bell />
                            @endif
                            <livewire:auth.components.user-dropdown />
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200">{{ __('layouts.login') }}</a>
                            <a href="{{ route('register') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200">{{ __('layouts.register') }}</a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            @auth
                <div id="mobile-menu" class="hidden sm:hidden border-t border-gray-200 dark:border-gray-700">
                    <div class="pt-2 pb-3 space-y-1">
                        <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('dashboard') ? 'border-indigo-500 text-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 dark:text-indigo-300' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200' }} transition-colors duration-200">
                            {{ __('layouts.dashboard') }}
                        </a>
                        <a href="{{ route('organizations.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('organizations.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 dark:text-indigo-300' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200' }} transition-colors duration-200">
                            {{ __('layouts.organizations') }}
                        </a>
                        <a href="{{ route('blueprints.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('blueprints.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 dark:text-indigo-300' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200' }} transition-colors duration-200">
                            {{ __('layouts.blueprints') }}
                        </a>
                        @if(config('marketplace.enabled', false))
                        <a href="{{ route('marketplace.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('marketplace.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 dark:text-indigo-300' : 'border-transparent text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-800 dark:hover:text-gray-200' }} transition-colors duration-200">
                            {{ __('layouts.marketplace') }}
                        </a>
                        @endif
                    </div>
                </div>
            @endauth
        </nav>

        <!-- Page Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toast Container -->
    <div 
        x-data="{ toasts: [] }"
        x-on:notify.window="let id = Date.now(); toasts.push({message: $event.detail.message, id}); setTimeout(() => toasts = toasts.filter(t => t.id !== id), 3000)"
        class="fixed top-4 right-4 z-50 space-y-2"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div 
                x-show="true"
                x-transition:enter="toast-enter"
                x-transition:leave="toast-exit"
                class="bg-gray-800 text-white px-4 py-3 rounded-lg shadow-lg flex items-center space-x-2 max-w-sm"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span x-text="toast.message" class="text-sm font-medium"></span>
            </div>
        </template>
    </div>

    <!-- Confirmation Dialog -->
    <div
        x-data
        x-show="$store.confirm.show"
        x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
            x-show="$store.confirm.show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="$store.confirm.cancel()"
        ></div>

        <!-- Modal -->
        <div
            x-show="$store.confirm.show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-md w-full mx-4 p-6 z-10"
        >
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('layouts.confirm_title') }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line" x-text="$store.confirm.message"></p>
                </div>
            </div>
            <div class="mt-6 flex justify-end space-x-3">
                <button
                    @click="$store.confirm.cancel()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-gray-400"
                >
                    {{ __('layouts.confirm_cancel') }}
                </button>
                <button
                    @click="$store.confirm.confirm()"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    x-text="$store.confirm.confirmText"
                ></button>
            </div>
        </div>
    </div>

    <!-- Copy to Clipboard Script -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('copy-to-clipboard', ({ text }) => {
                navigator.clipboard.writeText(text).catch(err => {
                    console.error('{{ __('shared.copy_error') }}', err);
                    // Fallback para navegadores que no soportan clipboard API
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                });
            });
        });

        // Alpine.js Confirmation Dialog Store
        document.addEventListener('alpine:init', () => {
            Alpine.store('confirm', {
                show: false,
                message: '',
                confirmText: '{{ __('layouts.confirm_delete') }}',
                onConfirm: null,

                ask({ message, confirmText = '{{ __('layouts.confirm_delete') }}', onConfirm }) {
                    this.message = message;
                    this.confirmText = confirmText;
                    this.show = true;
                    this.onConfirm = onConfirm;
                },

                confirm() {
                    this.show = false;
                    const cb = this.onConfirm;
                    this.onConfirm = null;
                    if (cb) cb();
                },

                cancel() {
                    this.show = false;
                    this.onConfirm = null;
                }
            });
        });
    </script>

    @livewireScripts

    {{-- Flash messages as toast notifications (must run AFTER Alpine is loaded) --}}
    @if(session('success') || session('error'))
    <script>
        document.addEventListener('alpine:initialized', () => {
            @if(session('success'))
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: @json(session('success')) }
                }));
            @endif
            @if(session('error'))
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message: @json(session('error')) }
                }));
            @endif
        });
    </script>
    @endif
</body>
</html>
