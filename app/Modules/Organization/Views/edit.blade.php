@extends('layouts.app')

@section('title', 'Editar ' . $organization->name)

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a>
            <span>/</span>
            <a href="{{ route('organizations.show', $organization->slug) }}" class="hover:text-gray-700">{{ $organization->name }}</a>
            <span>/</span>
            <span class="text-gray-900">Editar</span>
        </div>

        <h1 class="text-2xl font-bold mb-6">Editar Organización</h1>

        <form method="POST" action="{{ route('organizations.update', $organization->slug) }}" class="bg-white p-6 rounded-lg shadow space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $organization->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Mi Empresa" required>
                @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $organization->slug) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm" required>
                <p class="mt-1 text-xs text-gray-500">Identificador único para URLs.</p>
                @error('slug') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-between items-center pt-4 border-t">
                <a href="{{ route('organizations.show', $organization->slug) }}" class="text-sm text-gray-500 hover:text-gray-700">
                    ← Cancelar y volver
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                    </svg>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
@endsection
