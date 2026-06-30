<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%234f46e5'/%3E%3Ccircle cx='16' cy='15' r='7' stroke='white' stroke-width='1.5' fill='none'/%3E%3Cline x1='16' y1='6' x2='16' y2='8' stroke='white' stroke-width='1.2' stroke-linecap='round'/%3E%3Cline x1='16' y1='22' x2='16' y2='24' stroke='white' stroke-width='1.2' stroke-linecap='round'/%3E%3Cline x1='7' y1='15' x2='9' y2='15' stroke='white' stroke-width='1.2' stroke-linecap='round'/%3E%3Cline x1='23' y1='15' x2='25' y2='15' stroke='white' stroke-width='1.2' stroke-linecap='round'/%3E%3Cline x1='9.6' y1='8.6' x2='11' y2='10' stroke='white' stroke-width='0.8' stroke-linecap='round'/%3E%3Cline x1='21' y1='10' x2='22.4' y2='8.6' stroke='white' stroke-width='0.8' stroke-linecap='round'/%3E%3Cline x1='9.6' y1='21.4' x2='11' y2='20' stroke='white' stroke-width='0.8' stroke-linecap='round'/%3E%3Cline x1='21' y1='20' x2='22.4' y2='21.4' stroke='white' stroke-width='0.8' stroke-linecap='round'/%3E%3Ccircle cx='16' cy='15' r='2' fill='white'/%3E%3Cpath d='M14 7 L16 5 L18 7' stroke='white' stroke-width='1.2' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3Crect x='13' y='25' width='6' height='2' rx='1' fill='white'/%3E%3Ccircle cx='16' cy='26' r='0.8' fill='%234f46e5'/%3E%3C/svg%3E">
    <link rel="apple-touch-icon" sizes="180x180" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'%3E%3Crect width='180' height='180' rx='32' fill='%234f46e5'/%3E%3Ccircle cx='90' cy='85' r='42' stroke='white' stroke-width='8' fill='none'/%3E%3Cline x1='90' y1='35' x2='90' y2='48' stroke='white' stroke-width='7' stroke-linecap='round'/%3E%3Cline x1='90' y1='122' x2='90' y2='135' stroke='white' stroke-width='7' stroke-linecap='round'/%3E%3Cline x1='40' y1='85' x2='53' y2='85' stroke='white' stroke-width='7' stroke-linecap='round'/%3E%3Cline x1='127' y1='85' x2='140' y2='85' stroke='white' stroke-width='7' stroke-linecap='round'/%3E%3Ccircle cx='90' cy='85' r='12' fill='white'/%3E%3Cpath d='M75 40 L90 20 L105 40' stroke='white' stroke-width='7' stroke-linecap='round' stroke-linejoin='round' fill='none'/%3E%3Crect x='72' y='145' width='36' height='12' rx='6' fill='white'/%3E%3C/svg%3E">

    <title>@yield('title', __('landing.site_title'))</title>

    <meta name="description" content="@yield('meta_description', strip_tags(__('landing.hero_subtitle')))">
    <meta name="keywords" content="vault, environment variables, developer tools, devops, blueprints, laravel, env">

    <!-- Open Graph -->
    <meta property="og:title" content="@yield('og_title', __('landing.site_title'))">
    <meta property="og:description" content="@yield('og_description', strip_tags(__('landing.hero_subtitle')))">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">

    <!-- Twitter -->
    <meta name="twitter:title" content="@yield('og_title', __('landing.site_title'))">
    <meta name="twitter:description" content="@yield('og_description', strip_tags(__('landing.hero_subtitle')))">

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
        .revealed .reveal,
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
<body class="font-sans antialiased min-h-screen flex flex-col bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-300">
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
                    <svg class="w-10 h-10" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
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
                    <span>{{ config('app.name', 'CoVa') }}</span>
                </a>

                <!-- Center nav links -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#demo" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                        {{ __('landing.cta_secondary') }}
                    </a>
                    <a href="#pricing" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                        {{ __('landing.nav_pricing') }}
                    </a>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-3">
                    <livewire:shared.theme-toggle />
                    <x-locale-switcher />

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors">
                                {{ __('landing.go_to_dashboard') }}
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
    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('landing.partials.footer')

    @livewireScripts

    <script>
        document.addEventListener('alpine:init', () => {
            // Terminal component
            Alpine.data('terminal', (customLines = null) => ({
                lines: [],
                currentLine: 0,
                currentChar: 0,
                finished: false,
                running: false,
                content: customLines || [
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
