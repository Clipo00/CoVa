@php
    /** @var \Illuminate\Support\Collection $variables */
    $canViewSecrets = $canViewSecrets ?? false;
@endphp

<div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
    <button type="button" @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
        <div class="flex items-center space-x-3">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.env_variables') }}</h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('blueprint.variable_count', ['count' => $variables->count()]) }}</span>
        </div>
        <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
        @if($variables->isEmpty())
            <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                <p>{{ __('blueprint.variables_empty') }}</p>
                <p class="text-sm mt-1">{{ __('blueprint.variables_empty_hint') }}</p>
            </div>
        @else
            @php
                $groupedVars = $variables->groupBy(fn($v) => $v->section ?? 'General');
                $sectionColors = [];
                foreach($groupedVars as $section => $vars) {
                    $firstVar = $vars->first();
                    $sectionColors[$section] = $firstVar->section_color ?? '#6b7280';
                }
            @endphp

            <div class="space-y-5">
                @foreach($groupedVars as $section => $vars)
                    @php
                        $color = $sectionColors[$section] ?? '#6b7280';
                    @endphp
                    <div class="relative">
                        {{-- Section Header --}}
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-3 h-3 rounded-full" style="background-color: {{ $color }}"></span>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 font-mono">{{ $section }}</span>
                            <span class="text-xs text-gray-400">{{ __('blueprint.variable_count', ['count' => $vars->count()]) }}</span>
                        </div>
                        
                        {{-- Variables list with left border --}}
                        <div class="pl-4 space-y-1" style="border-left: 2px solid {{ $color }}33">
                            @foreach($vars as $variable)
                                <div class="flex items-center gap-3 py-2 px-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <span class="text-sm font-mono text-gray-900 dark:text-gray-100 min-w-[140px]">{{ $variable->key }}</span>
                                    <span class="text-xs text-gray-400">=</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400 flex-1">
                                        @if($variable->is_secret && !$canViewSecrets)
                                            <span class="text-gray-400 tracking-wider">{{ __('blueprint.secret_value') }}</span>
                                        @else
                                            {{ $variable->default_value ?? '-' }}
                                        @endif
                                    </span>
                                    <div class="flex items-center gap-2">
                                        @if($variable->type === 'fixed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                                {{ __('blueprint.var_type_fixed') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                                {{ __('blueprint.var_type_empty') }}
                                            </span>
                                        @endif
                                        @if($variable->is_interactive)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                                {{ __('blueprint.var_interactive') }}
                                            </span>
                                        @endif
                                        @if($variable->is_secret)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300">
                                                {{ __('blueprint.var_secret') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
