@extends('layouts.app')

@section('title', $blueprint->title)

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a>
            <span>/</span>
            <a href="{{ route('organizations.show', $blueprint->organization->slug) }}" class="hover:text-gray-700">{{ $blueprint->organization->name }}</a>
            <span>/</span>
            <span class="text-gray-900">{{ $blueprint->title }}</span>
        </div>

        {{-- Header --}}
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $blueprint->title }}</h1>
                    @if($blueprint->category)
                        <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
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
                    <a href="{{ route('blueprints.edit', $blueprint->uuid) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Editar
                    </a>
                    @can('delete', $blueprint)
                        <form method="POST" action="{{ route('blueprints.destroy', $blueprint->uuid) }}" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este blueprint?');">
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
                <p class="text-gray-600 mt-2">{{ $blueprint->description }}</p>
            @endif

            <div class="mt-4 flex items-center space-x-2 text-sm text-gray-500">
                <span>UUID:</span>
                <code class="bg-gray-100 px-2 py-1 rounded font-mono text-xs">{{ $blueprint->uuid }}</code>
            </div>
        </div>

        {{-- Variables Section --}}
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Variables de Entorno</h2>
                <span class="text-sm text-gray-500">{{ $blueprint->variables->count() }} variables</span>
            </div>

            @if($blueprint->variables->isEmpty())
                <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg">
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
                        <h3 class="text-sm font-medium text-gray-700 mb-2 flex items-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-xs">
                                {{ $section }}
                            </span>
                            <span class="ml-2 text-xs text-gray-400">{{ $vars->count() }} variable{{ $vars->count() > 1 ? 's' : '' }}</span>
                        </h3>
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Key</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tipo</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Valor</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Interactivo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach($vars as $variable)
                                        <tr>
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono text-gray-900">{{ $variable->key }}</td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $variable->type === 'fixed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $variable->type }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if($variable->is_secret)
                                                    <span class="text-gray-400">••••••••</span>
                                                @else
                                                    {{ $variable->default_value ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                @if($variable->is_interactive)
                                                    <span class="text-indigo-600 font-medium">Sí</span>
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

        {{-- Tabs Config JSON Preview --}}
        @if(!empty($blueprint->tabs_config))
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Configuración de Pestañas</h2>
                <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm font-mono text-gray-700">{{ json_encode($blueprint->tabs_config, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>
@endsection
