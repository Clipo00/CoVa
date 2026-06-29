@extends('layouts.app')

@section('title', __('blueprint.page_title'))

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">{{ __('blueprint.heading') }}</h1>
            @if($hasAvailableOrg)
                <a href="{{ route('blueprints.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                    {{ __('blueprint.new_button') }}
                </a>
            @elseif(!$userHasOrganizations)
                <div
                    x-data="{ show: false }"
                    class="relative"
                    @mouseenter="show = true"
                    @mouseleave="show = false"
                    @focusin="show = true"
                    @focusout="show = false"
                >
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed focus:outline-none"
                        disabled
                        aria-describedby="no-orgs-tooltip"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                        </svg>
                        {{ __('blueprint.new_button') }}
                    </button>
                    <div
                        id="no-orgs-tooltip"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 translate-y-1"
                        class="absolute right-0 mt-2 w-72 bg-gray-800 text-white text-xs rounded-lg py-3 px-3 shadow-xl z-20"
                        style="display: none;"
                    >
                        <p class="mb-2">{{ __('blueprint.no_orgs') }}</p>
                        <a href="{{ route('organizations.create') }}" class="text-indigo-400 hover:text-indigo-300 underline">{{ __('organization.create_first_link') }}</a>
                        <div class="absolute -top-1 right-6 w-2 h-2 bg-gray-800 rotate-45"></div>
                    </div>
                </div>
            @else
                <div class="relative group">
                    <span class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
                        </svg>
                        {{ __('blueprint.new_button') }}
                    </span>
                    <div class="absolute right-0 mt-2 w-64 bg-gray-800 text-white text-xs rounded py-2 px-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-10">
                        {{ __('blueprint.all_orgs_limit') }}
                    </div>
                </div>
            @endif
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

        <livewire:blueprint.tables.blueprint-list :public-only="request()->has('public')" />
    </div>
@endsection