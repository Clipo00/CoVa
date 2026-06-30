@extends('layouts.app')

@section('title', __('dashboard.page_title'))

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ __('dashboard.heading') }}</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-300">{{ __('dashboard.welcome', ['name' => auth()->user()->name]) }}</p>
        </div>

        {{-- Deleted Organizations Banner --}}
        @if($deletedOrganizations->count() > 0)
            <div class="mb-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-red-800">{{ __('dashboard.deleted_organizations') }}</h2>
                    <span class="text-sm text-red-600 bg-red-100 px-2 py-1 rounded">{{ $deletedOrganizations->count() }}</span>
                </div>
                <div class="space-y-4">
                    @foreach($deletedOrganizations as $org)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 flex items-center justify-between shadow-sm">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $org->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.deleted_time', ['time' => $org->deleted_at->diffForHumans()]) }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                @php
                                    $activeOrgsCount = auth()->user()->organizations()->count();
                                    $canRestore = $activeOrgsCount < auth()->user()->plan->max_organizations_per_user;
                                @endphp
                                @if($canRestore)
                                    <form method="POST" action="{{ route('organizations.restore', $org->slug) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                            </svg>
                                            {{ __('dashboard.restore_button') }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded">
                                        {{ __('dashboard.org_limit_reached', ['max' => auth()->user()->plan->max_organizations_per_user]) }}
                                    </span>
                                @endif
                                @if(auth()->user()->isOwnerOf($org))
                                    <form method="POST" action="{{ route('organizations.force-destroy', $org->slug) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message: '{{ __('dashboard.delete_confirm') }}', onConfirm(){ f.submit(); }})">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-700 text-sm font-medium rounded-md text-red-700 bg-white dark:bg-gray-800 hover:bg-red-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            {{ __('dashboard.force_delete_button') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    {{ __('dashboard.force_delete_warning') }}
                </p>
            </div>
        @endif

        {{-- Stats Row --}}
        @if($organizations->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('dashboard.stats_organizations') }}</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalOrgs }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('dashboard.stats_blueprints') }}</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $totalBlueprints }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('dashboard.stats_favorites') }}</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $favoritesCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('dashboard.stats_plan') }}</h3>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $plan?->name ?? '—' }}</p>
                </div>
            </div>
        @endif

        @if($organizations->isEmpty() && $deletedOrganizations->isEmpty())
            {{-- Sin organizaciones: CTA grande --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-12 text-center">
                    <div class="mx-auto h-16 w-16 text-gray-400 mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('dashboard.empty_heading') }}</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-6 max-w-md mx-auto">
                        {{ __('dashboard.empty_desc1') }}
                        {{ __('dashboard.empty_desc2') }}
                    </p>
                    <a href="{{ route('organizations.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('dashboard.empty_cta') }}
                    </a>
                </div>
            </div>
        @else
            {{-- Con organizaciones: Lista + opción de crear más --}}
            <div class="mb-6 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ __('dashboard.my_organizations') }}</h2>
                @if($canCreateMore)
                    <a href="{{ route('organizations.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        {{ __('dashboard.new_organization_button') }}
                    </a>
                @else
                    <span class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-2 rounded-md">
                        {{ __('dashboard.org_limit_reached', ['max' => $plan->max_organizations_per_user]) }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($organizations as $organization)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $organization->name }}</h3>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($organization->pivot->role === 'owner') bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200
                                    @elseif($organization->pivot->role === 'maintainer') bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200
                                    @else bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 @endif">
                                    {{ __('organization.role_' . $organization->pivot->role) }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $organization->slug }}</p>

                            <div class="flex items-center space-x-4 mb-4 text-sm text-gray-500 dark:text-gray-400">
                                <span>{{ __('dashboard.card_blueprints', ['count' => $organization->blueprints_count ?? 0]) }}</span>
                                <span>{{ __('dashboard.card_members', ['count' => $organization->members_count ?? 0]) }}</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-400">
                                    {{ __('dashboard.created_time', ['time' => $organization->created_at->diffForHumans()]) }}
                                </span>
                                <a href="{{ route('organizations.show', $organization->slug) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium">
                                    {{ __('dashboard.view_details') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(!$canCreateMore)
                <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                {!! __('dashboard.plan_limit_warning', ['plan' => e($plan->name)]) !!}
                                <a href="#" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                    {{ __('dashboard.upgrade_plan') }}
                                </a>
                                {{ __('dashboard.to_create_more') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection