@extends('layouts.app')

@section('title', __('auth.profile_title'))

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('auth.profile_heading') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('auth.profile_description') }}</p>
    </div>

    <div
        x-data="{ activeTab: window.location.hash ? window.location.hash.substring(1) : 'datos' }"
        x-init="$watch('activeTab', value => history.replaceState(null, '', '#' + value))"
    >
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="flex space-x-8" aria-label="Tabs">
                <button
                    @click="activeTab = 'datos'"
                    :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'datos', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600': activeTab !== 'datos' }"
                    class="px-1 py-3 text-sm font-medium border-b-2 transition-colors duration-200"
                    role="tab"
                >
                    {{ __('auth.profile_tab_datos') }}
                </button>
                <button
                    @click="activeTab = 'cuenta'"
                    :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'cuenta', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600': activeTab !== 'cuenta' }"
                    class="px-1 py-3 text-sm font-medium border-b-2 transition-colors duration-200"
                    role="tab"
                >
                    {{ __('auth.profile_tab_cuenta') }}
                </button>
                <button
                    @click="activeTab = 'seguridad'"
                    :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'seguridad', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600': activeTab !== 'seguridad' }"
                    class="px-1 py-3 text-sm font-medium border-b-2 transition-colors duration-200"
                    role="tab"
                >
                    {{ __('auth.profile_tab_seguridad') }}
                </button>
            </nav>
        </div>

        <!-- Datos + Cuenta: UserProfileForm (rendered once, sections controlled by Alpine activeTab) -->
        <div x-show="activeTab === 'datos' || activeTab === 'cuenta'" x-cloak>
            <livewire:auth.forms.user-profile-form />
        </div>

        <!-- Seguridad: API Tokens -->
        <div x-show="activeTab === 'seguridad'" x-cloak>
            <div class="max-w-2xl mx-auto">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('auth.api_tokens') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('auth.profile_seguridad_description') }}</p>
                    <livewire:auth.api-token-manager />
                </div>
            </div>
        </div>
    </div>
@endsection
