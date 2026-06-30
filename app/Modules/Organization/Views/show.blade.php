@extends('layouts.app')

@section('title', $organization->name)

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb / Volver --}}
        <div class="mb-6">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                {{ __('organization.back_to_dashboard') }}
            </a>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $organization->name }}</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $organization->slug }}</p>
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    @can('update', $organization)
                        <a href="{{ route('organizations.edit', $organization->slug) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{ __('organization.edit_link') }}
                        </a>
                    @endcan
                    @can('delete', $organization)
                        <form method="POST" action="{{ route('organizations.destroy', $organization->slug) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message: '{{ __('organization.delete_confirm') }}', onConfirm(){ f.submit(); }})">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-700 shadow-sm text-sm font-medium rounded-md text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ __('organization.delete_button') }}
                            </button>
                        </form>
                    @endcan
                    @php
                        $currentUserRole = $organization->owner_id === auth()->id()
                            ? 'owner'
                            : ($organization->members->find(auth()->id())?->pivot->role ?? 'developer');
                        $roleBadgeColors = match($currentUserRole) {
                            'owner'       => 'bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200',
                            'maintainer'  => 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200',
                            default       => 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200',
                        };
                    @endphp
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $roleBadgeColors }}">
                        {{ __('organization.role_' . $currentUserRole) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Stats / Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('organization.blueprints_count') }}</h3>
                <p class="text-3xl font-bold text-gray-900">{{ $organization->blueprints()->count() }}</p>
                <a href="{{ route('blueprints.index', ['org' => $organization->slug]) }}" class="mt-4 inline-block text-sm text-indigo-600 hover:text-indigo-800">
                    {{ __('organization.view_blueprints') }}
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('organization.members_count') }}</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $organization->members()->count() }}</p>
                <a href="{{ route('organizations.members', $organization->slug) }}" class="mt-4 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                    {{ __('organization.manage_members') }}
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ __('organization.plan_label') }}</h3>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $organization->plan->name }}</p>
                <p class="mt-4 text-sm text-gray-400">
                    {{ __('organization.max_blueprints_text', ['max' => $maxBlueprints]) }}
                </p>
            </div>
        </div>

        @if(!$canCreateBlueprint)
            <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            {!! __('organization.limit_warning', ['max' => $maxBlueprints, 'plan' => e($organization->plan->name)]) !!}
                        </p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            {{ __('organization.limit_hint') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Blueprints --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('organization.recent_blueprints') }}</h2>
                @if($canCreateBlueprint)
                    <a href="{{ route('blueprints.create', ['org' => $organization->slug]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm hover:bg-indigo-700">
                        {{ __('organization.new_blueprint_button') }}
                    </a>
                @else
                    <div class="text-right">
                        <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                            </svg>
                            {{ __('organization.limit_reached') }}
                        </span>
                    </div>
                @endif
            </div>

            @if($organization->blueprints()->count() === 0)
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <p>{{ __('organization.no_blueprints') }}</p>
                    @if($canCreateBlueprint)
                        <a href="{{ route('blueprints.create', ['org' => $organization->slug]) }}" class="mt-2 inline-block text-indigo-600 hover:text-indigo-800">
                            {{ __('organization.create_first_blueprint') }}
                        </a>
                    @endif
                </div>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($organization->blueprints()->latest()->limit(5)->get() as $blueprint)
                        <li class="py-3">
                            <a href="{{ route('blueprints.show', $blueprint->slug) }}" class="block hover:bg-gray-50 dark:hover:bg-gray-700 -mx-4 px-4 py-2 rounded">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ $blueprint->title }}</span>
                                    <div class="flex items-center space-x-2">
                                        @if($blueprint->category)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                                                {{ $blueprint->category->name }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $blueprint->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                @if($blueprint->description)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($blueprint->description, 100) }}</p>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
