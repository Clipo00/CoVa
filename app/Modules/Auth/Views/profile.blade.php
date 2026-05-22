@extends('layouts.app')

@section('title', __('auth.profile_title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('auth.profile_heading') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('auth.profile_description') }}</p>
    </div>

    <livewire:auth.forms.user-profile-form />
@endsection