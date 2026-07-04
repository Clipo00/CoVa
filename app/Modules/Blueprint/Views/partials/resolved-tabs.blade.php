@php
    $agentMd = $resolvedTabs->getAgentMdContent();
    $extensions = $resolvedTabs->getVscodeExtensions();
    $installCommand = $resolvedTabs->getVscodeInstallCommand();
    $mcpServers = $resolvedTabs->getMcpServers();

    // Build tab list — only include tabs that have content
    $tabs = [];
    if ($agentMd) {
        $tabs[] = ['id' => 'ai-context', 'label' => __('blueprint.agent_context'), 'icon' => 'agent'];
    }
    if (count($extensions) > 0) {
        $tabs[] = ['id' => 'vscode', 'label' => __('blueprint.preview_tab_extensions'), 'icon' => 'extensions'];
    }
    if (!empty($mcpServers)) {
        $tabs[] = ['id' => 'mcp', 'label' => __('blueprint.preview_tab_mcp'), 'icon' => 'mcp'];
    }
@endphp

@if(count($tabs) > 0)
    <div x-data="{
        activeTab: '{{ $tabs[0]['id'] }}',
    }" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 overflow-hidden">
        {{-- Tab Navigation --}}
        <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
            @foreach($tabs as $tab)
                <button type="button"
                    @click="activeTab = '{{ $tab['id'] }}'"
                    :class="activeTab === '{{ $tab['id'] }}'
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                    class="flex-1 min-w-0 whitespace-nowrap px-4 py-3 text-sm font-medium text-center border-b-2 transition-colors"
                >
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Tab Content --}}
        <div class="p-4">
            {{-- Agent Context Tab --}}
            @if($agentMd)
                <div x-show="activeTab === 'ai-context'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">{{ __('blueprint.agent_md_badge') }}</span>
                        </div>
                        <livewire:shared.copy-to-clipboard
                            :text="$agentMd"
                            :label="__('blueprint.copy_button')"
                            :success-message="__('blueprint.agent_md_copied')"
                        />
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 overflow-x-auto max-h-96 overflow-y-auto">
                        <pre class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-wrap font-mono">{{ $agentMd }}</pre>
                    </div>
                </div>
            @endif

            {{-- VSCode Extensions Tab --}}
            @if(count($extensions) > 0)
                <div x-show="activeTab === 'vscode'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">{{ count($extensions) }}</span>
                        <livewire:shared.copy-to-clipboard
                            :text="$installCommand"
                            :label="__('blueprint.copy_install_command')"
                            :success-message="__('blueprint.command_copied')"
                        />
                    </div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($extensions as $ext)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-mono bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                {{ $ext }}
                            </span>
                        @endforeach
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                        <code class="text-sm text-gray-600 dark:text-gray-300 font-mono break-all">{{ $installCommand }}</code>
                    </div>
                </div>
            @endif

            {{-- MCP Servers Tab --}}
            @if(!empty($mcpServers))
                <div x-show="activeTab === 'mcp'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="space-y-3">
                        @foreach($mcpServers['mcp_servers'] ?? [] as $server)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $server['name'] }}</span>
                                </div>
                                <code class="text-xs text-gray-600 dark:text-gray-300 block font-mono break-all">
                                    {{ $server['command'] }}
                                    @if(!empty($server['args']))
                                        {{ implode(' ', array_map(fn($a) => "'" . $a . "'", is_array($server['args'] ?? []) ? $server['args'] : [])) }}
                                    @endif
                                </code>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
