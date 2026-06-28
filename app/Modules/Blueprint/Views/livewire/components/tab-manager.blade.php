<div class="space-y-4">
    {{-- Tabs List --}}
    @forelse($tabs as $index => $tab)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $tab['type'] === 'vscode_extensions' ? ' bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200' : ($tab['type'] === 'mcp_servers' ? ' bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200' : ($tab['type'] === 'scripts' ? ' bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200' : ' bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200')) }}">
                    {{ $availableTabTypes[$tab['type']] ?? __('blueprint.tab_type_unknown') }}
                </span>
                <div class="flex items-center space-x-2">
                    @if($index > 0)
                            <button type="button" wire:click="moveTab({{ $index }}, -1)" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="{{ __('blueprint.move_up') }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                        </button>
                    @endif
                    @if($index < count($tabs) - 1)
                            <button type="button" wire:click="moveTab({{ $index }}, 1)" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="{{ __('blueprint.move_down') }}">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                    @endif
                    <button type="button" wire:click="removeTab({{ $index }})" class="p-1 text-red-400 hover:text-red-600 dark:hover:text-red-300" title="{{ __('blueprint.tab_delete') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            {{-- VSCode Extensions Config --}}
            @if($tab['type'] === 'vscode_extensions')
                @php
                    $extensions = $tab['config']['extensions'] ?? [];
                @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        {{ __('blueprint.extensions_label') }}
                    </label>
                    <textarea
                        wire:change="updateVscodeExtensions({{ $index }}, $event.target.value)"
                        rows="3"
                        class="block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                        placeholder="{{ __('blueprint.extensions_placeholder') }}"
                    >{{ implode("\n", $extensions) }}</textarea>
                    @if(!empty($extensions))
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.extensions_count', ['count' => count($extensions)]) }}</p>
                    @endif
                </div>
            @endif

            {{-- MCP Servers Config --}}
            @if($tab['type'] === 'mcp_servers')
                @php
                    $servers = $tab['config']['servers'] ?? [];
                @endphp
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('blueprint.mcp_servers_label') }}</label>
                    @foreach($servers as $serverIndex => $server)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-md p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('blueprint.server_label', ['index' => $serverIndex + 1]) }}</span>
                                <button type="button" wire:click="removeMcpServer({{ $index }}, {{ $serverIndex }})" class="text-red-400 hover:text-red-600 text-xs">
                                    {{ __('blueprint.server_delete') }}
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.server_name_label') }}</label>
                                    <input
                                        type="text"
                                        wire:change="updateMcpServerField({{ $index }}, {{ $serverIndex }}, 'name', $event.target.value)"
                                        value="{{ $server['name'] ?? '' }}"
                                        class="block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="{{ __('blueprint.server_name_placeholder') }}"
                                    />
                                </div>
                                <div>
                                        <label class="block text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.server_command_label') }}</label>
                                    <input
                                        type="text"
                                        wire:change="updateMcpServerField({{ $index }}, {{ $serverIndex }}, 'command', $event.target.value)"
                                        value="{{ $server['command'] ?? '' }}"
                                        class="block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="{{ __('blueprint.server_command_placeholder') }}"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.server_args_label') }}</label>
                                <input
                                    type="text"
                                    wire:change="updateMcpServerField({{ $index }}, {{ $serverIndex }}, 'args', $event.target.value)"
                                    value="{{ implode(' ', is_array($server['args'] ?? []) ? $server['args'] : []) }}"
                                    class="block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="{{ __('blueprint.server_args_placeholder') }}"
                                />
                            </div>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addMcpServer({{ $index }})" class="inline-flex items-center px-3 py-1.5 border border-dashed border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-700/50 dark:hover:bg-gray-700">
                        <svg class="-ml-1 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        {{ __('blueprint.server_add_button') }}
                    </button>
                </div>
            @endif

            {{-- Scripts Config --}}
            @if($tab['type'] === 'scripts')
                @php
                    $scripts = $tab['config']['scripts'] ?? [];
                @endphp
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ __('blueprint.scripts_label') }}
                    </label>
                    <p class="text-xs text-amber-600 dark:text-amber-400">
                        {{ __('blueprint.scripts_doc_only') }}
                    </p>
                    @foreach($scripts as $scriptIndex => $script)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-md p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('blueprint.script_label', ['index' => $scriptIndex + 1]) }}</span>
                                <button type="button" wire:click="removeScript({{ $index }}, {{ $scriptIndex }})" class="text-red-400 hover:text-red-600 text-xs">
                                    {{ __('blueprint.script_delete') }}
                                </button>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.script_command_label') }}</label>
                                <input
                                    type="text"
                                    wire:change="updateScriptField({{ $index }}, {{ $scriptIndex }}, 'command', $event.target.value)"
                                    value="{{ $script['command'] ?? '' }}"
                                    class="block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                                    placeholder="{{ __('blueprint.script_command_placeholder') }}"
                                />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.script_description_label') }}</label>
                                <input
                                    type="text"
                                    wire:change="updateScriptField({{ $index }}, {{ $scriptIndex }}, 'description', $event.target.value)"
                                    value="{{ $script['description'] ?? '' }}"
                                    class="block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="{{ __('blueprint.script_description_placeholder') }}"
                                />
                            </div>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addScript({{ $index }})" class="inline-flex items-center px-3 py-1.5 border border-dashed border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-700/50 dark:hover:bg-gray-700">
                        <svg class="-ml-1 mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        {{ __('blueprint.script_add_button') }}
                    </button>
                </div>
            @endif

            {{-- AI Context Config --}}
            @if($tab['type'] === 'ai_context')
                @php
                    $presets = $tab['config']['presets'] ?? [];
                    $skills = $tab['config']['skills'] ?? [];
                    $customRules = $tab['config']['custom_rules'] ?? '';
                @endphp
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ __('blueprint.code_presets') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($availablePresetNames as $preset)
                                <button
                                    type="button"
                                    wire:click="togglePreset({{ $index }}, '{{ $preset }}')"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                                        {{ in_array($preset, $presets) ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-200 ring-1 ring-indigo-300 dark:ring-indigo-700' : ' bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                                >
                                    {{ __('blueprint.preset_' . str_replace('-', '_', $preset)) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">{{ __('blueprint.skills_label') }}</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($availableSkillNames as $skill)
                                <button
                                    type="button"
                                    wire:click="toggleSkill({{ $index }}, '{{ $skill }}')"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium transition-colors
                                        {{ in_array($skill, $skills) ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-200 ring-1 ring-indigo-300 dark:ring-indigo-700' : ' bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
                                >
                                    {{ __('blueprint.skill_' . str_replace('-', '_', $skill)) }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">{{ __('blueprint.custom_rules') }}</label>
                        <textarea
                            wire:change="updateCustomRules({{ $index }}, $event.target.value)"
                            rows="3"
                            class="block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                            placeholder="{{ __('blueprint.custom_rules_placeholder') }}"
                        >{{ $customRules }}</textarea>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <p>{{ __('blueprint.tabs_empty') }}</p>
            <p class="text-sm mt-1">{{ __('blueprint.tabs_empty_hint') }}</p>
        </div>
    @endforelse

    {{-- Duplicate Tab Error --}}
    @if($tabError)
        <div class="rounded-md bg-red-50 dark:bg-red-900/30 p-3 border border-red-200 dark:border-red-800">
            <p class="text-sm text-red-700 dark:text-red-300">{{ $tabError }}</p>
        </div>
    @endif

    {{-- Add Tab Dropdown --}}
    <div class="flex flex-wrap gap-2">
        @foreach($availableTabTypes as $type => $label)
            <button
                type="button"
                wire:click="addTab('{{ $type }}')"
                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:bg-gray-700/50 dark:hover:bg-gray-700"
            >
                <svg class="-ml-1 mr-2 h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ __('blueprint.add_tab') }} {{ $label }}
            </button>
        @endforeach
    </div>
</div>