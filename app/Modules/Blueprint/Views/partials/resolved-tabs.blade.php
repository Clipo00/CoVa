@php
    $agentMd = $resolvedTabs->getAgentMdContent();
    $extensions = $resolvedTabs->getVscodeExtensions();
    $installCommand = $resolvedTabs->getVscodeInstallCommand();
    $mcpServers = $resolvedTabs->getMcpServers();
@endphp

{{-- Agent Context Section (collapsible) --}}
@if($agentMd)
    <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
        <div class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <button type="button" @click="open = !open" class="flex items-center space-x-3 flex-1 text-left">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.agent_context') }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">{{ __('blueprint.agent_md_badge') }}</span>
            </button>
            <div class="flex items-center space-x-3">
                <livewire:shared.copy-to-clipboard
                    :text="$agentMd"
                    :label="__('blueprint.copy_button')"
                    :success-message="__('blueprint.agent_md_copied')"
                />
                <button type="button" @click="open = !open" class="p-1">
                    <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 overflow-x-auto">
                <pre class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-wrap font-mono">{{ $agentMd }}</pre>
            </div>
        </div>
    </div>
@endif

{{-- VSCode Extensions Section (collapsible) --}}
@if(count($extensions) > 0)
    <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
        <div class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <button type="button" @click="open = !open" class="flex items-center space-x-3 flex-1 text-left">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.vscode_extensions') }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">{{ count($extensions) }}</span>
            </button>
            <div class="flex items-center space-x-3">
                <livewire:shared.copy-to-clipboard
                    :text="$installCommand"
                    :label="__('blueprint.copy_install_command')"
                    :success-message="__('blueprint.command_copied')"
                />
                <button type="button" @click="open = !open" class="p-1">
                    <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($extensions as $ext)
                    <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-mono bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                        {{ $ext }}
                    </span>
                @endforeach
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                <code class="text-sm text-gray-600 dark:text-gray-300 font-mono break-all">{{ $installCommand }}</code>
            </div>
        </div>
    </div>
@endif

{{-- MCP Servers Section (collapsible) --}}
@if(!empty($mcpServers))
    <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
        <button type="button" @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <div class="flex items-center space-x-3">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.mcp_servers') }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200">{{ count($mcpServers['mcp_servers'] ?? []) }}</span>
            </div>
            <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
            <div class="space-y-3">
                @foreach($mcpServers['mcp_servers'] ?? [] as $server)
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $server['name'] }}</span>
                        </div>
                        <code class="text-sm text-gray-600 dark:text-gray-300 block font-mono">
                            {{ $server['command'] }}
                            @if(!empty($server['args']))
                                {{ implode(' ', array_map(fn($a) => "'" . $a . "'", is_array($server['args'] ?? []) ? $server['args'] : [])) }}
                            @endif
                        </code>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
