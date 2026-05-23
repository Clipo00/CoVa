@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'bg-gray-900 dark:bg-gray-950 rounded-xl border border-gray-700/50 shadow-2xl overflow-hidden ' . $class]) }}
     x-data="terminal"
     role="region"
     aria-label="{{ __('landing.terminal_aria_label') }}"
>
    {{-- Terminal header --}}
    <div class="flex items-center gap-1.5 px-4 py-3 bg-gray-800/50 border-b border-gray-700/50">
        <span class="w-3 h-3 rounded-full bg-red-500"></span>
        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
        <span class="w-3 h-3 rounded-full bg-green-500"></span>
        <span class="ml-2 text-xs text-gray-400 font-mono">{{ __('landing.terminal_title') }}</span>
    </div>

    {{-- Terminal body --}}
    <div class="p-4 sm:p-6 font-mono text-sm leading-relaxed min-h-[200px]">
        <template x-if="!finished">
            <div>
                <template x-for="(line, index) in lines" :key="index">
                    <div>
                        <span :class="lineClass(index)" x-text="line"></span>
                        <span x-show="index === lines.length - 1 && !finished" class="terminal-cursor inline-block w-2 h-4 bg-emerald-400 ml-0.5 animate-pulse"></span>
                    </div>
                </template>
            </div>
        </template>

        {{-- Static view (for reduced motion / post-animation) --}}
        <template x-if="finished">
            <div>
                <template x-for="(line, index) in lines" :key="index">
                    <div>
                        <span :class="lineClass(index)" x-text="line"></span>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
