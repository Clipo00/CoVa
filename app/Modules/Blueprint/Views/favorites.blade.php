@extends('layouts.app')

@section('title', __('blueprint.favorites_title'))

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('blueprint.favorites_heading') }}</h1>
            <span class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                {{ __('blueprint.favorites_count', ['count' => $favoriteBlueprints->count()]) }}
            </span>
        </div>

        @if($favoriteBlueprints->isEmpty())
            <div class=" bg-white dark:bg-gray-800 shadow rounded-lg p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('blueprint.favorites_empty') }}</h3>
                <p class="text-gray-500 mb-4">{{ __('blueprint.favorites_empty_desc') }}</p>
                <a href="{{ route('blueprints.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/40 hover:bg-indigo-200 dark:hover:bg-indigo-700">
                    {{ __('blueprint.explore_blueprints') }}
                </a>
            </div>
        @else
            <div class=" bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($favoriteBlueprints as $blueprint)
                        <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('blueprints.show', $blueprint->uuid) }}" class="block">
                                        <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">
                                            {{ $blueprint->title }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $blueprint->organization->name }}
                                            @if($blueprint->is_public)
                                                <span class="mx-1">·</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">
                                                    {{ __('blueprint.badge_public') }}
                                                </span>
                                            @endif
                                            @if($blueprint->category)
                                                <span class="mx-1">·</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">
                                                    {{ $blueprint->category->name }}
                                                </span>
                                            @endif
                                        </p>
                                        @if($blueprint->description)
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 truncate">
                                                {{ Str::limit($blueprint->description, 120) }}
                                            </p>
                                        @endif
                                    </a>
                                </div>
                                <div class="ml-4 flex-shrink-0 flex items-center space-x-3">
                                    <livewire:shared.copy-to-clipboard 
                                        :text="$blueprint->uuid" 
                                        :label="__('blueprint.uuid_label')"
                                        :success-message="__('blueprint.uuid_copied_short')"
                                    />
                                    <span class="text-xs text-gray-400">
                                        {{ $blueprint->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
