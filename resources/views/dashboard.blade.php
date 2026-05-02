@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-2 text-gray-600">Bienvenido de vuelta, {{ auth()->user()->name }}</p>
        </div>

        @if($organizations->isEmpty())
            {{-- Sin organizaciones: CTA grande --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <div class="mx-auto h-16 w-16 text-gray-400 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">No tienes organizaciones</h2>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        Para empezar a usar CoVa necesitas crear una organización. 
                        Una organización agrupa tus blueprints y te permite colaborar con tu equipo.
                    </p>
                    <a href="{{ route('organizations.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Crear mi primera organización
                    </a>
                </div>
            </div>
        @else
            {{-- Con organizaciones: Lista + opción de crear más --}}
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">Mis Organizaciones</h2>
                @if($canCreateMore)
                    <a href="{{ route('organizations.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Nueva Organización
                    </a>
                @else
                    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-2 rounded-md">
                        Límite de {{ $plan->max_organizations_per_user }} organizaciones alcanzado
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($organizations as $organization)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $organization->name }}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($organization->pivot->role === 'owner') bg-purple-100 text-purple-800
                                    @elseif($organization->pivot->role === 'maintainer') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ ucfirst($organization->pivot->role) }}
                                </span>
                            </div>
                            
                            <p class="text-sm text-gray-500 mb-4">{{ $organization->slug }}</p>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">
                                    Creada {{ $organization->created_at->diffForHumans() }}
                                </span>
                                <a href="{{ route('organizations.show', $organization->slug) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                    Ver detalles →
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(!$canCreateMore)
                <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Has alcanzado el límite de organizaciones de tu plan <strong>{{ $plan->name }}</strong>.
                                <a href="#" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                    Actualiza tu plan
                                </a> para crear más.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
