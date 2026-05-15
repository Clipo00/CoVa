<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CoVa') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
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
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Mobile menu button -->
                        <div class="flex items-center sm:hidden">
                            <button type="button" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" aria-label="Abrir menú" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                <span class="sr-only">Abrir menú</span>
                                <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>

                        <a href="{{ route('dashboard') }}" class="ml-4 sm:ml-0 text-xl font-bold text-gray-800 hover:text-indigo-600">
                            {{ config('app.name', 'CoVa') }}
                        </a>

                        @auth
                            <div class="hidden sm:ml-8 sm:flex sm:space-x-6">
                                <a href="{{ route('dashboard') }}" class="text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Dashboard
                                </a>
                                <a href="{{ route('organizations.index') }}" class="text-sm font-medium {{ request()->routeIs('organizations.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Organizaciones
                                </a>
                                <a href="{{ route('blueprints.index') }}" class="text-sm font-medium {{ request()->routeIs('blueprints.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Blueprints
                                </a>
                                <a href="{{ route('blueprints.deleted') }}" class="text-sm font-medium {{ request()->routeIs('blueprints.deleted') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Eliminados
                                </a>
                            </div>
                        @endauth
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <livewire:auth.components.user-dropdown />
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-800">Login</a>
                            <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-gray-800">Registro</a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            @auth
                <div id="mobile-menu" class="hidden sm:hidden border-t border-gray-200">
                    <div class="pt-2 pb-3 space-y-1">
                        <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('dashboard') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('organizations.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('organizations.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                            Organizaciones
                        </a>
                        <a href="{{ route('blueprints.index') }}" class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('blueprints.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800' }}">
                            Blueprints
                        </a>
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
        x-on:notify.window="toasts.push({message: $event.detail.message, id: Date.now()}); setTimeout(() => toasts = toasts.filter(t => t.id !== $event.detail.id), 3000)"
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

    <!-- Copy to Clipboard Script -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('copy-to-clipboard', ({ text }) => {
                navigator.clipboard.writeText(text).catch(err => {
                    console.error('Error al copiar:', err);
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
    </script>

    @livewireScripts
</body>
</html>
