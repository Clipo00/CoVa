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
                    {{ $organization->plan->max_blueprints_per_org }} blueprints máx.
                </p>
            </div>
        </div>

        {{-- Recent Blueprints --}}
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Blueprints recientes</h2>
                <a href="{{ route('blueprints.create', ['org' => $organization->id]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">
                    + Nuevo Blueprint
                </a>
            </div>

            @if($organization->blueprints()->count() === 0)
                <div class="text-center py-8 text-gray-500">
                    <p>No hay blueprints todavía.</p>
                    <a href="{{ route('blueprints.create', ['org' => $organization->id]) }}" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800">
                        Crea el primer blueprint
                    </a>
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
