@extends('layouts.app')

@section('title', __('organization.create_title'))

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">{{ __('organization.create_heading') }}</h1>
        <p class=" text-gray-600 dark:text-gray-300 mb-8">
            {{ __('organization.create_description') }}
        </p>

        <livewire:organization.forms.create-organization-form />
    </div>
@endsection
