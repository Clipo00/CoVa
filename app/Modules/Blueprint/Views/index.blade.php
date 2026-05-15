@extends('layouts.app')

@section('title', 'Blueprints')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Blueprints</h1>
            <a href="{{ route('blueprints.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Nuevo Blueprint
            </a>
        </div>

        <livewire:blueprint.tables.blueprint-list />
    </div>
@endsection