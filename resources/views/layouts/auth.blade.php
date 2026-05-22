<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CoVa') }} - @yield('title', __('layouts.site_title'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    {{-- Locale Switcher — visible tanto para guests como autenticados --}}
    <div class="fixed top-4 right-4 z-50">
        <x-locale-switcher />
    </div>

    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <a href="/" class="inline-block">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {{ config('app.name', 'CoVa') }}
                    </h1>
                </a>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    @yield('subtitle', __('layouts.app_subtitle'))
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                @yield('content')
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>