@extends('layouts.app')

@section('title', $organization->name)

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb / Volver --}}
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Volver al Dashboard
            </a>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Header --}}
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $organization->name }}</h1>
                    <p class="mt-1 text-sm text-gray-500">{{ $organization->slug }}</p>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    @can('update', $organization)
                        <a href="{{ route('organizations.edit', $organization->slug) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Editar
                        </a>
                    @endcan
                    @can('delete', $organization)
                        <form method="POST" action="{{ route('organizations.destroy', $organization->slug) }}" class="inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta organización?\n\nEsta acción es reversible desde el dashboard.\n\nPara eliminar permanentemente, ve al dashboard después de eliminar.');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Eliminar
                            </button>
                        </form>
                    @endcan
                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-purple-100 text-purple-800">
                        {{ ucfirst($organization->owner_id === auth()->id() ? 'Owner' : $organization->members->find(auth()->id())?->pivot->role ?? 'Member') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Stats / Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Blueprints</h3>
                <p class="text-3xl font-bold text-gray-900">{{ $organization->blueprints()->count() }}</p>
                <a href="{{ route('blueprints.index', ['org' => $organization->id]) }}" class="mt-4 inline-block text-sm text-indigo-600 hover:text-indigo-800">
                    Ver blueprints →
                </a>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Miembros</h3>
                <p class="text-3xl font-bold text-gray-900">{{ $organization->members()->count() }}</p>
                <a href="{{ route('organizations.members', $organization->slug) }}" class="mt-4 inline-block text-sm text-indigo-600 hover:text-indigo-800">
                    Gestionar miembros →
                </a>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Plan</h3>
                <p class="text-3xl font-bold text-gray-900">{{ $organization->plan->name }}</p>
                <p class="mt-4 text-sm text-gray-400">
                    {{ $maxBlueprints }} blueprints máx.
                </p>
            </div>
        </div>

        @if(!$canCreateBlueprint)
            <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Has alcanzado el límite de <strong>{{ $maxBlueprints }} blueprints</strong> de tu plan <strong>{{ $organization->plan->name }}</strong>.
                            Elimina un blueprint existente para poder crear uno nuevo.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Blueprints --}}
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Blueprints recientes</h2>
                @if($canCreateBlueprint)
                    <a href="{{ route('blueprints.create', ['org' => $organization->id]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">
                        + Nuevo Blueprint
                    </a>
                @else
                    <div class="text-right">
                        <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-gray-100 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                            </svg>
                            Límite alcanzado
                        </span>
                    </div>
                @endif
            </div>

            @if($organization->blueprints()->count() === 0)
                <div class="text-center py-8 text-gray-500">
                    <p>No hay blueprints todavía.</p>
                    @if($canCreateBlueprint)
                        <a href="{{ route('blueprints.create', ['org' => $organization->id]) }}" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800">
                            Crea el primer blueprint
                        </a>
                    @endif
                </div>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach($organization->blueprints()->latest()->limit(5)->get() as $blueprint)
                        <li class="py-3">
                            <a href="{{ route('blueprints.show', $blueprint->uuid) }}" class="block hover:bg-gray-50 -mx-4 px-4 py-2 rounded">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-indigo-600">{{ $blueprint->title }}</span>
                                    <span class="text-xs text-gray-400">{{ $blueprint->created_at->diffForHumans() }}</span>
                                </div>
                                @if($blueprint->description)
                                    <p class="text-sm text-gray-500 mt-1">{{ Str::limit($blueprint->description, 100) }}</p>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
