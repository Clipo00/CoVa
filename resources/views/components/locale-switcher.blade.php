<div x-data="{ open: false }" @click.away="open = false" class="relative">
    {{-- Trigger Button --}}
    <button
        type="button"
        @click="open = !open"
        class="flex items-center gap-1.5 px-2.5 py-1.5 text-sm font-medium rounded-md
            text-gray-600 dark:text-gray-400
            hover:text-gray-800 dark:hover:text-gray-200
            hover:bg-gray-100 dark:hover:bg-gray-700
            transition-colors duration-200
            focus:outline-none focus:ring-2 focus:ring-indigo-500"
        aria-label="{{ __('shared.locale') }}"
        title="{{ __('shared.locale') }}"
    >
        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="uppercase">{{ app()->getLocale() }}</span>
        <svg class="w-3 h-3 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-36 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-700 z-50 overflow-hidden"
        role="menu"
    >
        @foreach (['es', 'en'] as $localeCode)
            <a
                href="{{ route('locale.set', $localeCode) }}"
                class="flex items-center gap-2 px-4 py-2 text-sm transition-colors duration-150
                    {{ app()->getLocale() === $localeCode
                        ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                    }}"
                role="menuitem"
            >
                <span class="uppercase w-6 text-xs font-semibold text-gray-400 dark:text-gray-500">{{ $localeCode }}</span>
                <span>{{ __("shared.locale_{$localeCode}") }}</span>
                @if (app()->getLocale() === $localeCode)
                    <svg class="w-4 h-4 ml-auto text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                @endif
            </a>
        @endforeach
    </div>
</div>
