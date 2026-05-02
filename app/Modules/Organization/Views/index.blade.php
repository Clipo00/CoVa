@extends('layouts.app')

@section('title', 'Organizaciones')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Mis Organizaciones</h1>
        <a href="{{ route('organizations.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
            Nueva Organización
        </a>
    </div>

    <livewire:organization.tables.organization-list />
@endsection
