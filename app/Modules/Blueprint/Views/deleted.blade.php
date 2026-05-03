@extends('layouts.app')

@section('title', 'Blueprints Eliminados')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Volver al Dashboard
            </a>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Blueprints Eliminados</h1>
            <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                {{ $deletedBlueprints->count() }} eliminados
            </span>
        </div>

        @if($deletedBlueprints->isEmpty())
            <div class="bg-white shadow rounded-lg p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay blueprints eliminados</h3>
                <p class="text-gray-500">Los blueprints eliminados aparecerán aquí.</p>
            </div>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($deletedBlueprints as $blueprint)
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $blueprint->title }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $blueprint->organization->name }} · Eliminado {{ $blueprint->deleted_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    @if(auth()->user()->isOwnerOf($blueprint->organization))
                                        <form method="POST" action="{{ route('blueprints.restore', $blueprint->uuid) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                                </svg>
                                                Restaurar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">Solo el owner puede restaurar</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
