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
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center space-x-8">
                        <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800 hover:text-indigo-600">
                            {{ config('app.name', 'CoVa') }}
                        </a>

                        @auth
                            <div class="hidden sm:flex sm:space-x-6">
                                <a href="{{ route('dashboard') }}" class="text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Dashboard
                                </a>
                                <a href="{{ route('organizations.index') }}" class="text-sm font-medium {{ request()->routeIs('organizations.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Organizaciones
                                </a>
                                <a href="{{ route('blueprints.index') }}" class="text-sm font-medium {{ request()->routeIs('blueprints.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    Blueprints
                                </a>
                            </div>
                        @endauth
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                        Salir
                                    </button>
                                </form>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-800">Login</a>
                            <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-gray-800">Registro</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
