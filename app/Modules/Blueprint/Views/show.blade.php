@extends('layouts.app')

@section('title', $blueprint->title)

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Alpine.js download helper --}}
        @once
            @push('scripts')
                <script>
                    document.addEventListener('alpine:init', () => {
                        Alpine.magic('downloadTextFile', () => (content, filename) => {
                            const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                            const url = URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            URL.revokeObjectURL(url);
                        });
                    });
                </script>
            @endpush
        @endonce

        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('layouts.dashboard') }}</a>
            <span>/</span>
            <a href="{{ route('organizations.show', $blueprint->organization->slug) }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ $blueprint->organization->name }}</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</span>
        </div>

        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</h1>
                    @if($blueprint->tags->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            @foreach($blueprint->tags as $tag)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
                @php
                    $userOrgsWhereOwner = auth()->user()->organizations()->wherePivot('role', 'owner')->where('organizations.id', '!=', $blueprint->organization_id)->get();
                @endphp
                <div class="mt-4 sm:mt-0 flex items-center gap-1">
                    <livewire:shared.copy-to-clipboard
                        :text="$blueprint->uuid"
                        :label="__('blueprint.copy_uuid')"
                        :success-message="__('blueprint.uuid_copied')"
                    />

                    {{-- Transfer (modal) --}}
                    @if($userOrgsWhereOwner->count() > 0)
                        <div x-data="{ open: false }">
                            <button type="button" @click="open = true"
                                class="inline-flex items-center justify-center w-9 h-9 border border-gray-300 dark:border-gray-600 shadow-sm rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                                title="{{ __('blueprint.transfer_button') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </button>

                            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" @click.self="open = false" @keydown.escape.window="open = false">
                                <div class="fixed inset-0 bg-black/50"></div>
                                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 p-6 z-10" @click.stop>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('blueprint.transfer_button') }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('blueprint.transfer_select_org') }}</p>
                                    <form method="POST" action="{{ route('blueprints.transfer', $blueprint->uuid) }}" @submit="open = false">
                                        @csrf
                                        <select name="target_organization_id" required
                                            class="block w-full px-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-4">
                                            <option value="">{{ __('blueprint.transfer_to') }}</option>
                                            @foreach($userOrgsWhereOwner as $org)
                                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="open = false"
                                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                {{ __('shared.cancel') }}
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                {{ __('blueprint.transfer_button') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('blueprints.edit', $blueprint->slug) }}"
                        class="inline-flex items-center justify-center w-9 h-9 border border-gray-300 dark:border-gray-600 shadow-sm rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                        title="{{ __('blueprint.edit_button') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </a>

                    @php
                        $hasSecrets = $blueprint->variables->where('is_secret', true)->isNotEmpty();
                        $emailVerified = auth()->user()->hasVerifiedEmail();
                    @endphp

                    @if($hasSecrets && !$emailVerified)
                        <button type="button"
                            @click="$dispatch('notify', { message: '{{ __('blueprint.zip_email_unverified') }}' })"
                            class="inline-flex items-center justify-center w-9 h-9 border border-gray-300 dark:border-gray-600 shadow-sm rounded-md text-gray-400 dark:text-gray-500 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                            title="{{ __('blueprint.download_zip') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </button>
                    @elseif($hasSecrets)
                        <button type="button"
                            onclick="Alpine.store('confirm').ask({ message: '{{ __('blueprint.zip_download_encrypted') }}', confirmText: '{{ __('blueprint.zip_download_button') }}', onConfirm: function() { document.getElementById('download-zip-form').submit(); document.getElementById('zip-spinner').classList.remove('hidden'); setTimeout(function() { document.getElementById('zip-spinner').classList.add('hidden'); }, 5000); } })"
                            class="inline-flex items-center justify-center w-9 h-9 border border-gray-300 dark:border-gray-600 shadow-sm rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                            title="{{ __('blueprint.download_zip') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </button>
                    @else
                        <button type="button"
                            onclick="document.getElementById('download-zip-form').submit(); document.getElementById('zip-spinner').classList.remove('hidden'); setTimeout(() => document.getElementById('zip-spinner').classList.add('hidden'), 5000);"
                            class="inline-flex items-center justify-center w-9 h-9 border border-gray-300 dark:border-gray-600 shadow-sm rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                            title="{{ __('blueprint.download_zip') }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </button>
                    @endif

                    <form id="download-zip-form" method="POST" action="{{ route('blueprints.download', $blueprint->slug) }}" class="hidden">
                        @csrf
                    </form>

                    <div id="zip-spinner" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                        <div class="fixed inset-0 bg-black/50"></div>
                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 p-6 z-10 text-center">
                            <svg class="animate-spin h-10 w-10 text-indigo-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-gray-600 dark:text-gray-300">{{ __('blueprint.zip_loading') }}</p>
                        </div>
                    </div>

                    @can('publish', $blueprint)
                        @if($blueprint->is_public)
                        <form method="POST" action="{{ route('blueprints.publish', $blueprint->uuid) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ __('blueprint.publish_sync_confirm') }}', confirmText:'{{ __('blueprint.publish_sync_button') }}', onConfirm(){ f.submit(); }})">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center justify-center w-9 h-9 border border-transparent shadow-sm rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors"
                                title="{{ __('blueprint.publish_sync_button') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('blueprints.publish', $blueprint->uuid) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ __('blueprint.publish_confirm_warning') }}', confirmText:'{{ __('blueprint.publish_button') }}', onConfirm(){ f.submit(); }})">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center justify-center w-9 h-9 border border-transparent shadow-sm rounded-md text-white bg-emerald-600 hover:bg-emerald-700 transition-colors"
                                title="{{ __('blueprint.publish_button') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                        @endif
                    @endcan

                    @can('delete', $blueprint)
                        <form method="POST" action="{{ route('blueprints.destroy', $blueprint->uuid) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ __('blueprint.delete_confirm') }}', onConfirm(){ f.submit(); }})">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center justify-center w-9 h-9 border border-transparent shadow-sm rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors"
                                title="{{ __('blueprint.delete_button') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
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

            {{-- Vault Fetch Card --}}
            <div class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('blueprint.vault_fetch_label') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('blueprint.vault_fetch_hint') }}</p>
                    </div>
                </div>
                <div class="mt-3 flex items-center space-x-2">
                    <code class="flex-1 bg-white dark:bg-gray-800 px-3 py-2 rounded text-sm font-mono text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600 select-all">
                        cova vault:fetch {{ $blueprint->slug }}
                    </code>
                    <livewire:shared.copy-to-clipboard
                        :text="'cova vault:fetch ' . $blueprint->slug"
                        :label="__('blueprint.copy_button')"
                        :success-message="__('blueprint.vault_fetch_copied')"
                    />
                </div>
            </div>
        </div>

        {{-- Vote Section --}}
        @if(config('marketplace.enabled') && $blueprint->is_public)
            @can('vote', $blueprint)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 p-4 mb-6">
                    <div class="flex items-center justify-center space-x-6">
                        <form method="POST" action="{{ route('blueprints.vote', $blueprint->uuid) }}" class="inline">
                            @csrf
                            <input type="hidden" name="vote_type" value="up">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-600 dark:hover:text-green-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                {{ __('blueprint.vote_up') }}
                            </button>
                        </form>
                        <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $blueprint->aggregate_score ?? 0 }}</span>
                        <form method="POST" action="{{ route('blueprints.vote', $blueprint->uuid) }}" class="inline">
                            @csrf
                            <input type="hidden" name="vote_type" value="down">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                {{ __('blueprint.vote_down') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        @endif

        @php
            $extensions = $blueprintOutput->getVscodeExtensions();
            $installCommand = $blueprintOutput->getVscodeInstallCommand();
            $mcpServers = $blueprintOutput->getMcpServers();
            $agentMd = $blueprintOutput->getAgentMdContent();
            $scripts = $blueprintOutput->getScripts();
            $scriptsShell = $blueprintOutput->getScriptsShellScript();

            // Determine which tabs are available
            $hasVariables = $blueprint->variables->isNotEmpty();
            $hasAiContext = !empty($agentMd);
            $hasExtensions = count($extensions) > 0;
            $hasScripts = !empty($scripts);
            $hasMcpServers = !empty($mcpServers);

            // Build ordered tab list — first available is default
            $tabs = [];
            if ($hasVariables) $tabs[] = 'variables';
            if ($hasAiContext) $tabs[] = 'ai-context';
            if ($hasExtensions) $tabs[] = 'vscode';
            if ($hasScripts) $tabs[] = 'scripts';
            if ($hasMcpServers) $tabs[] = 'mcp';
            $defaultTab = $tabs[0] ?? 'variables';

            // Group variables by section for the variables tab
            $groupedVars = $blueprint->variables->groupBy(fn($v) => $v->section ?? 'General');
            $sectionColors = [];
            foreach($groupedVars as $section => $vars) {
                $rawColor = $vars->first()->section_color ?? '#6b7280';
                $sectionColors[$section] = preg_match('/^#[a-fA-F0-9]{6}$/', $rawColor) ? $rawColor : '#6B7280';
            }
        @endphp

        {{-- Expose segments data for download buttons (avoids Alpine attribute quoting issues) --}}
        @if($hasAiContext && count($segments) > 0)
            <script>window.__blueprintSegments = @json($segments);</script>
        @endif
        @if($hasAiContext)
            <script>window.__blueprintAgentMd = @json($agentMd);</script>
        @endif

        {{-- Tabbed Content Interface --}}
        <div x-data="{ tab: '{{ $defaultTab }}' }" class="mb-6">
            {{-- Tab Navigation --}}
            <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                @foreach($tabs as $tabId)
                    <button type="button"
                        @click="tab = '{{ $tabId }}'"
                        :class="tab === '{{ $tabId }}'
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="flex-1 min-w-0 whitespace-nowrap px-4 py-3 text-sm font-medium text-center border-b-2 transition-colors"
                    >
                        @if($tabId === 'variables')
                            {{ __('blueprint.env_variables') }}
                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                {{ $blueprint->variables->count() }}
                            </span>
                        @elseif($tabId === 'ai-context')
                            {{ __('blueprint.agent_context') }}
                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                {{ count($segments) }}
                            </span>
                        @elseif($tabId === 'vscode')
                            {{ __('blueprint.vscode_extensions') }}
                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300">
                                {{ count($extensions) }}
                            </span>
                        @elseif($tabId === 'scripts')
                            {{ __('blueprint.scripts_section') }}
                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                {{ count($scripts) }}
                            </span>
                        @elseif($tabId === 'mcp')
                            {{ __('blueprint.mcp_servers') }}
                            <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300">
                                {{ count($mcpServers['mcp_servers'] ?? []) }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Tab Content Panel --}}
            <div class="bg-white dark:bg-gray-800 rounded-b-xl rounded-tr-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 border-t-0 p-6">

                {{-- Variables Tab --}}
                <div x-show="tab === 'variables'" class="tab-panel">
                    @if($blueprint->variables->isEmpty())
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                            <p>{{ __('blueprint.variables_empty') }}</p>
                            <p class="text-sm mt-1">{{ __('blueprint.variables_empty_hint') }}</p>
                        </div>
                    @else
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.env_variables') }}</h3>
                            @if($envTemplate)
                                <button type="button" onclick="downloadTextFile(@json($envTemplate), '.env')" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ __('blueprint.download_env') }}
                                </button>
                            @endif
                        </div>
                        <div class="space-y-5">
                            @foreach($groupedVars as $section => $vars)
                                @php $color = $sectionColors[$section] ?? '#6B7280'; @endphp
                                <div class="relative">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $color }}"></span>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 font-mono">{{ $section }}</span>
                                        <span class="text-xs text-gray-400">{{ __('blueprint.variable_count', ['count' => $vars->count()]) }}</span>
                                    </div>
                                    <div class="pl-4 space-y-1" style="border-left: 2px solid {{ $color }}33">
                                        @foreach($vars as $variable)
                                            <div class="flex items-center gap-3 py-2 px-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                                <span class="text-sm font-mono text-gray-900 dark:text-gray-100 min-w-[140px]">{{ $variable->key }}</span>
                                                <span class="text-xs text-gray-400">=</span>
                                                <span class="text-sm text-gray-600 dark:text-gray-400 flex-1">
                                                    @if($variable->is_secret)
                                                        <span class="text-gray-400 tracking-wider">{{ __('blueprint.secret_value') }}</span>
                                                    @else
                                                        {{ $variable->default_value ?? '-' }}
                                                    @endif
                                                </span>
                                                <div class="flex items-center gap-2">
                                                    @if($variable->type === 'fixed')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">{{ __('blueprint.var_type_fixed') }}</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">{{ __('blueprint.var_type_empty') }}</span>
                                                    @endif
                                                    @if($variable->is_interactive)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">{{ __('blueprint.var_interactive') }}</span>
                                                    @endif
                                                    @if($variable->is_secret)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300">{{ __('blueprint.var_secret') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- AI Context Tab --}}
                <div x-show="tab === 'ai-context'" class="tab-panel">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">{{ __('blueprint.agent_md_badge') }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button type="button" onclick="downloadTextFile(window.__blueprintAgentMd, '.agent.md')" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('blueprint.download_agent_md') }}
                            </button>
                            <livewire:shared.copy-to-clipboard
                                :text="$agentMd ?? ''"
                                :label="__('blueprint.copy_button')"
                                :success-message="__('blueprint.agent_md_copied')"
                            />
                        </div>
                    </div>

                    @if(count($segments) > 0)
                        {{-- Individual segment panels using native <details> — zero Alpine, zero JS issues --}}
                        <div class="space-y-3">
                            @foreach($segments as $index => $segment)
                                <details open class="group border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden">
                                    <summary class="w-full px-4 py-3 flex items-center justify-between bg-gray-50 dark:bg-gray-700/30 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                        <div class="flex items-center space-x-3 min-w-0">
                                            <span class="text-sm font-mono font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $segment['name'] }}</span>
                                            <span class="text-xs text-gray-400 flex-shrink-0 hidden sm:inline">{{ $segment['filename'] }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2 flex-shrink-0 ml-2">
                                            <button type="button"
                                                onclick="event.preventDefault(); event.stopPropagation(); downloadTextFile(window.__blueprintSegments[{{ $index }}].content, window.__blueprintSegments[{{ $index }}].filename)"
                                                class="inline-flex items-center px-2 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition-colors cursor-pointer"
                                                title="{{ __('blueprint.download_segment') }}"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="hidden sm:inline">{{ __('blueprint.download_segment') }}</span>
                                            </button>
                                            <svg class="h-4 w-4 text-gray-400 transform transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </summary>
                                    <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700/50">
                                        <pre class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-wrap font-mono">{{ $segment['content'] }}</pre>
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-wrap font-mono">{{ $agentMd }}</pre>
                        </div>
                    @endif
                </div>

                {{-- VSCode Extensions Tab --}}
                <div x-show="tab === 'vscode'" class="tab-panel">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">{{ count($extensions) }}</span>
                        <livewire:shared.copy-to-clipboard
                            :text="$installCommand ?? ''"
                            :label="__('blueprint.copy_install_command')"
                            :success-message="__('blueprint.command_copied')"
                        />
                    </div>
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($extensions as $ext)
                            <span class="inline-flex items-center px-3 py-1 rounded-md text-sm font-mono bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                {{ $ext }}
                            </span>
                        @endforeach
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                        <code class="text-sm text-gray-600 dark:text-gray-300 font-mono break-all">{{ $installCommand }}</code>
                    </div>
                </div>

                {{-- Scripts Tab --}}
                <div x-show="tab === 'scripts'" class="tab-panel">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">{{ count($scripts) }}</span>
                        <livewire:shared.copy-to-clipboard
                            :text="$scriptsShell ?? ''"
                            :label="__('blueprint.copy_scripts_command')"
                            :success-message="__('blueprint.scripts_copied')"
                        />
                    </div>
                    <div class="space-y-2">
                        <ol class="list-decimal list-inside space-y-3">
                            @foreach($scripts as $script)
                                <li class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                                    <code class="text-sm font-mono text-gray-800 dark:text-gray-200 break-all">{{ $script['command'] }}</code>
                                    @if(!empty($script['description']))
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $script['description'] }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    <div class="mt-3">
                        <p class="text-xs text-amber-600 dark:text-amber-400">{{ __('blueprint.scripts_doc_only') }}</p>
                    </div>
                </div>

                {{-- MCP Servers Tab --}}
                <div x-show="tab === 'mcp'" class="tab-panel">
                    <div class="space-y-3">
                        @foreach($mcpServers['mcp_servers'] ?? [] as $server)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $server['name'] }}</span>
                                </div>
                                <code class="text-sm text-gray-600 dark:text-gray-300 block font-mono">
                                    {{ $server['command'] }}
                                    @if(!empty($server['args']))
                                        {{ implode(' ', array_map(fn($a) => "'" . $a . "'", is_array($server['args'] ?? []) ? $server['args'] : [])) }}
                                    @endif
                                </code>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Global download helper (also accessible outside Alpine scope) --}}
        <script>
            function downloadTextFile(content, filename) {
                const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
        </script>
    </div>
@endsection
