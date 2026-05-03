@extends('layouts.app')

@section('title', 'Editar ' . $blueprint->title)

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center space-x-2 text-sm text-gray-500">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a>
            <span>/</span>
            <a href="{{ route('organizations.show', $blueprint->organization->slug) }}" class="hover:text-gray-700">{{ $blueprint->organization->name }}</a>
            <span>/</span>
            <a href="{{ route('blueprints.show', $blueprint->uuid) }}" class="hover:text-gray-700">{{ $blueprint->title }}</a>
            <span>/</span>
            <span class="text-gray-900">Editar</span>
        </div>

        <h1 class="text-2xl font-bold mb-6">Editar Blueprint</h1>

        <livewire:blueprint.forms.blueprint-edit-form :blueprint="$blueprint" />
    </div>
@endsection
