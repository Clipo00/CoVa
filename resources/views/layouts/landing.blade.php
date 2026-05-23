<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('landing.hero_title')) — {{ config('app.name', 'CoVa') }}</title>

    <meta name="description" content="@yield('meta_description', __('landing.hero_title'))">
    <meta name="keywords" content="vault, environment variables, developer tools, devops, blueprints, laravel, env">

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', __('landing.hero_title'))">
    <meta property="og:description" content="@yield('og_description', __('landing.hero_subtitle'))">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', __('landing.hero_title'))">
    <meta name="twitter:description" content="@yield('og_description', __('landing.hero_subtitle'))">

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
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/landing.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }

        /* Scroll reveal animation */
        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0ms; }
        .reveal-delay-2 { transition-delay: 150ms; }
        .reveal-delay-3 { transition-delay: 300ms; }
        .reveal-delay-4 { transition-delay: 450ms; }

        /* Terminal syntax colors */
        .terminal-prompt { color: #63c5da; }
        .terminal-info { color: #a5b4fc; }
        .terminal-success { color: #4ade80; }

        @media (prefers-reduced-motion: reduce) {
            .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
            .terminal-cursor {
                animation: none;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <!-- Skip to content (a11y) -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-indigo-600 focus:text-white focus:rounded-lg focus:outline-none">
        {{ __('shared.skip_to_content') }}
    </a>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-40 bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-b border-gray-200/50 dark:border-gray-800/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="/" class="flex items-center space-x-2 text-xl font-bold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <svg class="w-8 h-8" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect width="32" height="32" rx="8" fill="currentColor" class="text-indigo-600"/>
                        <path d="M9 16l4 4 10-10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>{{ config('app.name', 'CoVa') }}</span>
                </a>

                <!-- Right side -->
                <div class="flex items-center space-x-3">
                    <livewire:shared.theme-toggle />
                    <x-locale-switcher />

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                {{ __('landing.login') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                                {{ __('landing.login') }}
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors shadow-sm">
                                    {{ __('landing.register') }}
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('landing.partials.footer')

    @livewireScripts

    <script>
        document.addEventListener('alpine:init', () => {
            // Terminal component
            Alpine.data('terminal', () => ({
                lines: [],
                currentLine: 0,
                currentChar: 0,
                finished: false,
                running: false,
                content: [
                    { text: '$ vault fetch cova-marketplace/laravel-inertia', cls: 'terminal-prompt' },
                    { text: '', cls: '' },
                    { text: '> Descargando blueprint...', cls: 'terminal-info' },
                    { text: '> Variables cargadas: 12', cls: 'terminal-info' },
                    { text: '> Archivos generados: .env, agent.md, .cursorrules', cls: 'terminal-info' },
                    { text: '', cls: '' },
                    { text: '✅ Entorno listo en 2.4s', cls: 'terminal-success' },
                ],

                init() {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        this.lines = this.content.map(l => l.text);
                        this.finished = true;
                        return;
                    }
                    this.startTyping();
                },

                startTyping() {
                    this.running = true;
                    this.lines = [];
                    this.currentLine = 0;
                    this.currentChar = 0;
                    this.typeLine();
                },

                typeLine() {
                    if (this.currentLine >= this.content.length) {
                        this.finished = true;
                        this.running = false;
                        setTimeout(() => this.startTyping(), 3000);
                        return;
                    }

                    const line = this.content[this.currentLine];
                    if (line.text === '') {
                        this.lines.push('');
                        this.currentLine++;
                        setTimeout(() => this.typeLine(), 200);
                        return;
                    }

                    if (this.currentChar < line.text.length) {
                        const currentText = line.text.slice(0, this.currentChar + 1);
                        // Replace last line with current progress
                        if (this.lines.length > this.currentLine) {
                            this.lines[this.currentLine] = currentText;
                        } else {
                            this.lines.push(currentText);
                        }
                        this.currentChar++;
                        const delay = line.text[this.currentChar - 1] === ' ' ? 40 : 25;
                        setTimeout(() => this.typeLine(), delay);
                    } else {
                        // Store class info for styling
                        this.lines[this.currentLine] = line.text;
                        this.currentLine++;
                        this.currentChar = 0;
                        setTimeout(() => this.typeLine(), 300);
                    }
                },

                lineClass(index) {
                    if (index < this.content.length) {
                        return this.content[index].cls || '';
                    }
                    return '';
                }
            }));

            // Scroll reveal
            Alpine.directive('reveal', (el, { expression }, { evaluateLater, effect }) => {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    el.classList.add('revealed');
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            el.classList.add('revealed');
                            observer.unobserve(el);
                        }
                    });
                }, { threshold: 0.1 });

                observer.observe(el);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
