<div x-data="{ activeTab: null }" class="space-y-4">
    {{-- Tabs List --}}
    <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-200">
        @forelse($tabs as $index => $tab)
            <div class="p-4" x-data="{ open: false }">
                {{-- Tab Header --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <button
                            type="button"
                            @click="open = !open"
                            class="text-left"
                        >
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $tab['type'] }}
                                </span>
                                <svg :class="{'rotate-180': open}" class="h-4 w-4 text-gray-400 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </button>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center space-x-2">
                        @if($index > 0)
                            <button
                                type="button"
                                wire:click="moveTab({{ $index }}, -1)"
                                class="p-1 text-gray-400 hover:text-gray-600"
                                title="Mover arriba"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                        @endif

                        @if($index < count($tabs) - 1)
                            <button
                                type="button"
                                wire:click="moveTab({{ $index }}, 1)"
                                class="p-1 text-gray-400 hover:text-gray-600"
                                title="Mover abajo"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        @endif

                        <button
                            type="button"
                            wire:click="removeTab({{ $index }})"
                            class="p-1 text-red-400 hover:text-red-600"
                            title="Eliminar"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Tab Content --}}
                <div x-show="open" x-collapse class="mt-4">
                    @switch($tab['type'])
                        @case('vscode_extensions')
                            @php
                                $extensions = $tab['config']['extensions'] ?? [];
                            @endphp
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Extensiones (una por línea)
                                </label>
                                <textarea
                                    wire:change="updateVscodeExtensions({{ $index }}, explode(',', event.target.value))"
                                    rows="4"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                                    placeholder="ext1&#10;ext2&#10;ext3"
                                >{{ implode("\n", $extensions) }}</textarea>
                                <p class="mt-1 text-sm text-gray-500">
                                    ID de extensiones separadas por nueva línea
                                </p>
                            </div>
                        @break

                        @case('mcp_servers')
                            @php
                                $servers = $tab['config']['servers'] ?? [];
                            @endphp
                            <div class="space-y-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    Servidores MCP
                                </label>
                                @forelse($servers as $serverIndex => $server)
                                    <div class="flex items-center space-x-2 bg-gray-50 p-3 rounded-md">
                                        <div class="flex-1 grid grid-cols-3 gap-2">
                                            <input
                                                type="text"
                                                wire:change="updateMcpServers({{ $index }}, array_map(fn($s, $i) => $i === {{ $serverIndex }} ? array_merge($s, ['name' => event.target.value]) : $s, {{ json_encode($servers) }}, array_keys({{ json_encode($servers) }})))"
                                                value="{{ $server['name'] }}"
                                                placeholder="Nombre"
                                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            />
                                            <input
                                                type="text"
                                                wire:change="updateMcpServers({{ $index }}, array_map(fn($s, $i) => $i === {{ $serverIndex }} ? array_merge($s, ['command' => event.target.value]) : $s, {{ json_encode($servers) }}, array_keys({{ json_encode($servers) }})))"
                                                value="{{ $server['command'] }}"
                                                placeholder="Comando"
                                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            />
                                            <input
                                                type="text"
                                                wire:change="updateMcpServers({{ $index }}, array_map(fn($s, $i) => $i === {{ $serverIndex }} ? array_merge($s, ['args' => explode(' ', event.target.value)]) : $s, {{ json_encode($servers) }}, array_keys({{ json_encode($servers) }})))"
                                                value="{{ implode(' ', $server['args'] ?? []) }}"
                                                placeholder="Args separados por espacio"
                                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            />
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">No hay servidores configurados</p>
                                @endforelse
                            </div>
                        @break

                        @case('ai_context')
                            @php
                                $presets = $tab['config']['presets'] ?? [];
                                $skills = $tab['config']['skills'] ?? [];
                                $customRules = $tab['config']['custom_rules'] ?? '';
                            @endphp
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Presets de código
                                    </label>
                                    <div class="space-y-2">
                                        @foreach(['psr12', 'solid', 'clean-architecture'] as $preset)
                                            <label class="inline-flex items-center mr-4">
                                                <input
                                                    type="checkbox"
                                                    wire:change="updateAiContext({{ $index }}, array_filter(array_map(fn($p) => $p === '{{ $preset }}' ? ($event.target.checked ? $p : null) : $p, {{ json_encode($presets) }}), fn($p) => $p !== null), {{ json_encode($skills) }}, '{{ addslashes($customRules) }}')"
                                                    @if(in_array($preset, $presets)) checked @endif
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                />
                                                <span class="ml-2 text-sm text-gray-700">{{ strtoupper($preset) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Skills
                                    </label>
                                    <div class="space-y-2">
                                        @foreach(['stripe', 'tailwind'] as $skill)
                                            <label class="inline-flex items-center mr-4">
                                                <input
                                                    type="checkbox"
                                                    wire:change="updateAiContext({{ $index }}, {{ json_encode($presets) }}, array_filter(array_map(fn($s) => $s === '{{ $skill }}' ? ($event.target.checked ? $s : null) : $s, {{ json_encode($skills) }}), fn($s) => $s !== null), '{{ addslashes($customRules) }}')"
                                                    @if(in_array($skill, $skills)) checked @endif
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                />
                                                <span class="ml-2 text-sm text-gray-700 capitalize">{{ $skill }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Reglas custom (Markdown)
                                    </label>
                                    <textarea
                                        wire:change="updateAiContext({{ $index }}, {{ json_encode($presets) }}, {{ json_encode($skills) }}, event.target.value)"
                                        rows="4"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono"
                                        placeholder="Reglas adicionales para el agente..."
                                    >{{ $customRules }}</textarea>
                                </div>
                            </div>
                        @break
                    @endswitch
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p>No hay pestañas configuradas</p>
                <p class="text-sm mt-1">Agregá una pestaña para comenzar</p>
            </div>
        @endforelse
    </div>

    {{-- Add Tab Dropdown --}}
    <div x-data="{ open: false }" class="relative">
        <button
            type="button"
            @click="open = !open"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
        >
            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Agregar Pestaña
        </button>

        <div
            x-show="open"
            @click.away="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute left-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
        >
            <div class="py-1">
                @foreach($availableTabTypes as $type => $label)
                    <button
                        type="button"
                        wire:click="addTab('{{ $type }}'); open = false"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
