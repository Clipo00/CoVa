<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CoVa') }} - {{ __('errors.419_title') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="text-8xl font-bold text-orange-500 mb-4">419</div>
            <h1 class="text-2xl font-semibold text-gray-800 mb-2">{{ __('errors.419_heading') }}</h1>
            <p class="text-gray-600 mb-8">
                {{ __('errors.419_message') }}
            </p>
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                {{ __('errors.419_login') }}
            </a>
        </div>
        <p class="mt-12 text-sm text-gray-400">{{ config('app.name', 'CoVa') }} &mdash; {{ __('errors.footer') }}</p>
    </div>
</body>
</html>
