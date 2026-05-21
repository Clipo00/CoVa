@extends('layouts.app')

@section('title', 'Perfil')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Mi Perfil</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona tu información personal</p>
    </div>

    <livewire:auth.forms.user-profile-form />
@endsection