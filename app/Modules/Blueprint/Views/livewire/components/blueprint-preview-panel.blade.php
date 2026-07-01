<div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 overflow-hidden">
    {{-- Preview Header (collapsible toggle) --}}
    <button type="button" @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </div>
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.live_preview') }}</h2>
        </div>
        <svg :class="{'rotate-180': open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Preview Body (collapsible) --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
        @php
            $resolvedTabs = $this->resolvedTabs;
        @endphp

        @if($resolvedTabs === null && empty($this->variables))
            {{-- Empty state --}}
            <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <p>{{ __('blueprint.tabs_empty') }}</p>
                <p class="text-sm mt-1">{{ __('blueprint.tabs_empty_hint') }}</p>
            </div>
        @else
            {{-- Variables preview --}}
            @if(!empty($this->variables))
                @php
                    $variablesCollection = collect(array_map(function($var) {
                        return (object) [
                            'key' => $var['key'] ?? '',
                            'type' => $var['type'] ?? 'fixed',
                            'default_value' => $var['default_value'] ?? '',
                            'is_interactive' => (bool)($var['is_interactive'] ?? false),
                            'is_secret' => (bool)($var['is_secret'] ?? false),
                            'section' => $var['section'] ?? null,
                            'section_color' => $var['section_color'] ?? null,
                        ];
                    }, $this->variables));
                @endphp
                @include('blueprint::partials.variables-list', [
                    'variables' => $variablesCollection,
                    'canViewSecrets' => $this->canViewSecrets,
                ])
            @endif

            {{-- Resolved tabs preview --}}
            @if($resolvedTabs !== null)
                @include('blueprint::partials.resolved-tabs', [
                    'resolvedTabs' => $resolvedTabs,
                ])
            @endif
        @endif
    </div>
</div>
