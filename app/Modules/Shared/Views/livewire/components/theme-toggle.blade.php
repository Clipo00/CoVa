<div 
    x-data="{ 
        dark: localStorage.getItem('theme') === 'dark',
        init() {
            this.apply();
        },
        toggle() {
            this.dark = !this.dark;
            this.apply();
        },
        apply() {
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            if (this.dark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }"
    x-init="init()"
>
    <button
        type="button"
        @click="toggle()"
        class="relative w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-500"
        aria-label="{{ __('shared.theme_toggle') }}"
        title="{{ __('shared.theme_toggle') }}"
    >
        <!-- Sol -->
        <svg
            x-show="!dark"
            x-cloak
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 translate-y-5 rotate-45 scale-50"
            x-transition:enter-end="opacity-100 translate-y-0 rotate-0 scale-100"
            x-transition:leave="transition ease-in duration-700"
            x-transition:leave-start="opacity-100 translate-y-0 rotate-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-5 -rotate-45 scale-50"
            class="w-6 h-6 text-amber-700 absolute"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <circle cx="12" cy="12" r="5" />
            <line x1="12" y1="1" x2="12" y2="3" />
            <line x1="12" y1="21" x2="12" y2="23" />
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
            <line x1="1" y1="12" x2="3" y2="12" />
            <line x1="21" y1="12" x2="23" y2="12" />
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
        </svg>
        
        <!-- Luna -->
        <svg
            x-show="dark"
            x-cloak
            x-transition:enter="transition ease-out duration-700"
            x-transition:enter-start="opacity-0 -translate-y-5 rotate-45 scale-50"
            x-transition:enter-end="opacity-100 translate-y-0 rotate-0 scale-100"
            x-transition:leave="transition ease-in duration-700"
            x-transition:leave-start="opacity-100 translate-y-0 rotate-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-5 -rotate-45 scale-50"
            class="w-6 h-6 text-indigo-300 absolute"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
        </svg>
    </button>
</div>

