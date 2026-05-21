@extends('layouts.app')

@section('title', $blueprint->title)

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">Dashboard</a>
            <span>/</span>
            <a href="{{ route('organizations.show', $blueprint->organization->slug) }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ $blueprint->organization->name }}</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</span>
        </div>

        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</h1>
                    @if($blueprint->category)
                        <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">
                            {{ $blueprint->category->name }}
                        </span>
                    @endif
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    <livewire:shared.copy-to-clipboard
                        :text="$blueprint->uuid"
                        label="Copiar UUID"
                        success-message="UUID copiado al portapapeles"
                    />
                    @php
                        $userOrgsWhereOwner = auth()->user()->organizations()->wherePivot('role', 'owner')->where('organizations.id', '!=', $blueprint->organization_id)->get();
                    @endphp
                    @if($userOrgsWhereOwner->count() > 0)
                        <form method="POST" action="{{ route('blueprints.transfer', $blueprint->uuid) }}" x-data class="inline flex items-center space-x-2" @submit.prevent="const f=$el; const s=$refs.targetOrg; if (s.value) { f.submit(); } else { $store.confirm.ask({message:'Selecciona una organización destino', confirmText:'Entendido', onConfirm(){ s.focus(); }}); }">
                            @csrf
                            <select x-ref="targetOrg" name="target_organization_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                                <option value="">Transferir a...</option>
                                @foreach($userOrgsWhereOwner as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Transferir
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('blueprints.edit', $blueprint->uuid) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Editar
                    </a>
                    @can('delete', $blueprint)
                        <form method="POST" action="{{ route('blueprints.destroy', $blueprint->uuid) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'¿Estás seguro de que quieres eliminar este blueprint?', onConfirm(){ f.submit(); }})">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Eliminar
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            @if($blueprint->description)
                <p class="text-gray-600 dark:text-gray-300 mt-2">{{ $blueprint->description }}</p>
            @endif

            <div class="mt-4 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <span>UUID:</span>
                <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono text-xs">{{ $blueprint->uuid }}</code>
            </div>
        </div>

        {{-- Variables Section (collapsible) --}}
        <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
            <button type="button" @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <div class="flex items-center space-x-3">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Variables de Entorno</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $blueprint->variables->count() }} variable{{ $blueprint->variables->count() !== 1 ? 's' : '' }}</span>
                </div>
                <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
                @if($blueprint->variables->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <p>No hay variables configuradas.</p>
                        <p class="text-sm mt-1">Las variables se definen al crear o editar el blueprint.</p>
                    </div>
                @else
                    @php
                        $groupedVars = $blueprint->variables->groupBy(fn($v) => $v->section ?? 'General');
                    @endphp

                    @foreach($groupedVars as $section => $vars)
                        <div class="mb-6 last:mb-0">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2 flex items-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs">
                                    {{ $section }}
                                </span>
                                <span class="ml-2 text-xs text-gray-400">{{ $vars->count() }} variable{{ $vars->count() > 1 ? 's' : '' }}</span>
                            </h3>
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Key</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Tipo</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Valor</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Interactivo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        @foreach($vars as $variable)
                                            <tr>
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $variable->key }}</td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $variable->type === 'fixed' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' : 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-200' }}">
                                                        {{ $variable->type }}
                                                    </span>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                    @if($variable->is_secret)
                                                        <span class="text-gray-400">••••••••</span>
                                                    @else
                                                        {{ $variable->default_value ?? '-' }}
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    @if($variable->is_interactive)
                                                        <span class="text-indigo-600 dark:text-indigo-400 font-medium">Sí</span>
                                                    @else
                                                        <span class="text-gray-400">No</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        @php
            $extensions = $blueprintOutput->getVscodeExtensions();
            $installCommand = $blueprintOutput->getVscodeInstallCommand();
            $mcpServers = $blueprintOutput->getMcpServers();
            $agentMd = $blueprintOutput->getAgentMdContent();
        @endphp

        {{-- Agent Context Section (collapsible) --}}
        @if($agentMd)
            <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
                <div class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <button type="button" @click="open = !open" class="flex items-center space-x-3 flex-1 text-left">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Agent Context</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">agent.md</span>
                    </button>
                    <div class="flex items-center space-x-3">
                        <livewire:shared.copy-to-clipboard
                            :text="$agentMd"
                            label="Copiar"
                            success-message="agent.md copiado al portapapeles"
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
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">VSCode Extensions</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">{{ count($extensions) }}</span>
                    </button>
                    <div class="flex items-center space-x-3">
                        <livewire:shared.copy-to-clipboard
                            :text="$installCommand"
                            label="Copiar install command"
                            success-message="Comando copiado al portapapeles"
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
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">MCP Servers</h2>
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
    </div>
@endsection
