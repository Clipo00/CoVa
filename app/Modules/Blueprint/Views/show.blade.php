@extends('layouts.app')

@section('title', $blueprint->title)

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('layouts.dashboard') }}</a>
            <span>/</span>
            <a href="{{ route('organizations.show', $blueprint->organization->slug) }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ $blueprint->organization->name }}</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</span>
        </div>

        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</h1>
                    @if($blueprint->category)
                        <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">
                            {{ $blueprint->category->name }}
                        </span>
                    @endif
                    @if($blueprint->is_public)
                        <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">
                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ __('blueprint.badge_public') }}
                        </span>
                    @else
                        <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            {{ __('blueprint.badge_private') }}
                        </span>
                    @endif
                </div>
                <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                    <livewire:shared.copy-to-clipboard
                        :text="$blueprint->uuid"
                        :label="__('blueprint.copy_uuid')"
                        :success-message="__('blueprint.uuid_copied')"
                    />
                    @php
                        $userOrgsWhereOwner = auth()->user()->organizations()->wherePivot('role', 'owner')->where('organizations.id', '!=', $blueprint->organization_id)->get();
                    @endphp
                    @if($userOrgsWhereOwner->count() > 0)
                        <form method="POST" action="{{ route('blueprints.transfer', $blueprint->uuid) }}" x-data class="inline flex items-center space-x-2" @submit.prevent="const f=$el; const s=$refs.targetOrg; if (s.value) { f.submit(); } else { $store.confirm.ask({message:'{{ __('blueprint.transfer_select_org') }}', confirmText:'{{ __('shared.understood') }}', onConfirm(){ s.focus(); }}); }">
                            @csrf
                            <select x-ref="targetOrg" name="target_organization_id" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
                                <option value="">{{ __('blueprint.transfer_to') }}</option>
                                @foreach($userOrgsWhereOwner as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                {{ __('blueprint.transfer_button') }}
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('blueprints.edit', $blueprint->uuid) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{ __('blueprint.edit_button') }}
                    </a>
                    @can('delete', $blueprint)
                        <form method="POST" action="{{ route('blueprints.destroy', $blueprint->uuid) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ $blueprint->is_public ? __('blueprint.delete_confirm_public') : __('blueprint.delete_confirm') }}', onConfirm(){ f.submit(); }})">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ __('blueprint.delete_button') }}
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            @if($blueprint->description)
                <p class="text-gray-600 dark:text-gray-300 mt-2">{{ $blueprint->description }}</p>
            @endif

            <div class="mt-4 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <span>{{ __('blueprint.uuid_label') }}:</span>
                <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono text-xs">{{ $blueprint->uuid }}</code>
            </div>
        </div>

        {{-- Variables Section (collapsible) --}}
        @include('blueprint::partials.variables-list', [
            'variables' => $blueprint->variables,
            'canViewSecrets' => false,
        ])

        {{-- Resolved Tabs: Agent Context, VSCode Extensions, MCP Servers --}}
        @include('blueprint::partials.resolved-tabs', [
            'resolvedTabs' => new \App\Modules\Blueprint\DTOs\ResolvedTabs($blueprintOutput->tabs),
        ])
    </div>
@endsection
