@extends('layouts.app')

@section('title', __('blueprint.edit_title', ['title' => $blueprint->title]))

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb --}}
        <nav class="mb-6 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">{{ __('layouts.dashboard') }}</a>
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            <a href="{{ route('organizations.show', $blueprint->organization->slug) }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">{{ $blueprint->organization->name }}</a>
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            <a href="{{ route('blueprints.show', $blueprint->uuid) }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">{{ $blueprint->title }}</a>
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ __('blueprint.edit_breadcrumb') }}</span>
        </nav>

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('blueprint.edit_heading') }}</h1>
            </div>
        </div>

        <livewire:blueprint.forms.blueprint-edit-form :blueprint="$blueprint" />
    </div>
@endsection
