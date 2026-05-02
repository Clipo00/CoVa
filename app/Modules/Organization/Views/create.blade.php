@extends('layouts.app')

@section('title', 'Crear Organización')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Crear tu primera organización</h1>
        <p class="text-gray-600 mb-8">
            Una organización agrupa tus blueprints y permite colaborar con tu equipo.
        </p>

        <livewire:organization.forms.create-organization-form />
    </div>
@endsection
