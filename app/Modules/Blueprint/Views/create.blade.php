@extends('layouts.app')

@section('title', 'Nuevo Blueprint')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Crear Blueprint</h1>

        <livewire:blueprint.forms.blueprint-create-form :organization-id="request('org', 1)" />
    </div>
@endsection
