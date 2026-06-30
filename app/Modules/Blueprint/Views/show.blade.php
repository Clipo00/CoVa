@extends('layouts.app')

@section('title', $blueprint->title)

@section('content')
    @php
        $agentMdContent = $blueprintOutput->getAgentMdContent();
    @endphp

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
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</h1>
                    @if($blueprint->category)
                        <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">
                            {{ $blueprint->category->name }}
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
                            <select x-ref="targetOrg" name="target_organization_id" class="block w-full px-3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2">
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
                    <a href="{{ route('blueprints.edit', $blueprint->slug) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{ __('blueprint.edit_button') }}
                    </a>
                                    @can('publish', $blueprint)
                        @if($blueprint->is_public)
                        <form method="POST" action="{{ route('blueprints.publish', $blueprint->uuid) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ __('blueprint.publish_sync_confirm') }}', confirmText:'{{ __('blueprint.publish_sync_button') }}', onConfirm(){ f.submit(); }})">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                </svg>
                                {{ __('blueprint.publish_sync_button') }}
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('blueprints.publish', $blueprint->uuid) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ __('blueprint.publish_confirm_warning') }}', confirmText:'{{ __('blueprint.publish_button') }}', onConfirm(){ f.submit(); }})">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                                </svg>
                                {{ __('blueprint.publish_button') }}
                            </button>
                        </form>
                        @endif
                    @endcan
                    @can('delete', $blueprint)
                        <form method="POST" action="{{ route('blueprints.destroy', $blueprint->uuid) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ __('blueprint.delete_confirm') }}', onConfirm(){ f.submit(); }})">
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
                        vault fetch cova/{{ $blueprint->slug }}
                    </code>
                    <livewire:shared.copy-to-clipboard
                        :text="'vault fetch cova/' . $blueprint->slug"
                        :label="__('blueprint.copy_button')"
                        :success-message="__('blueprint.vault_fetch_copied')"
                    />
                </div>
            </div>
        </div>

        {{-- Vote Section --}}
        @if(config('marketplace.enabled') && $blueprint->is_public)
            @can('vote', $blueprint)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
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

        {{-- Variables Section (collapsible) --}}
        <div x-data="{ open: true, envContent: @json($envTemplate) }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
            <div class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <button type="button" @click="open = !open" class="flex items-center space-x-3 flex-1 text-left">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.env_variables') }}</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('blueprint.variable_count', ['count' => $blueprint->variables->count()]) }}</span>
                </button>
                <div class="flex items-center space-x-3">
                    @if($envTemplate)
                        <button type="button" @click="$downloadTextFile(envContent, '.env')" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors" title="{{ __('blueprint.download_env') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('blueprint.download_env') }}
                        </button>
                    @endif
                    <button type="button" @click="open = !open" class="p-1 flex-shrink-0">
                        <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>

            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
                @if($blueprint->variables->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <p>{{ __('blueprint.variables_empty') }}</p>
                        <p class="text-sm mt-1">{{ __('blueprint.variables_empty_hint') }}</p>
                    </div>
                @else
                    @php
                        $groupedVars = $blueprint->variables->groupBy(fn($v) => $v->section ?? 'General');
                        $sectionColors = [];
                        foreach($groupedVars as $section => $vars) {
                            $firstVar = $vars->first();
                            $rawColor = $firstVar->section_color ?? '#6b7280';
                            // Sanitize hex color: only allow valid 6-digit hex
                            $sectionColors[$section] = preg_match('/^#[a-fA-F0-9]{6}$/', $rawColor) ? $rawColor : '#6B7280';
                        }
                    @endphp

                    <div class="space-y-5">
                        @foreach($groupedVars as $section => $vars)
                            @php
                                $color = $sectionColors[$section] ?? '#6B7280';
                            @endphp
                            <div class="relative">
                                {{-- Section Header --}}
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $color }}"></span>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 font-mono">{{ $section }}</span>
                                    <span class="text-xs text-gray-400">{{ __('blueprint.variable_count', ['count' => $vars->count()]) }}</span>
                                </div>
                                
                                {{-- Variables list with left border --}}
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
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                                        {{ __('blueprint.var_type_fixed') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                                        {{ __('blueprint.var_type_empty') }}
                                                    </span>
                                                @endif
                                                @if($variable->is_interactive)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                                                        {{ __('blueprint.var_interactive') }}
                                                    </span>
                                                @endif
                                                @if($variable->is_secret)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300">
                                                        {{ __('blueprint.var_secret') }}
                                                    </span>
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
        </div>

        @php
            $extensions = $blueprintOutput->getVscodeExtensions();
            $installCommand = $blueprintOutput->getVscodeInstallCommand();
            $mcpServers = $blueprintOutput->getMcpServers();
            $agentMd = $blueprintOutput->getAgentMdContent();
            $scripts = $blueprintOutput->getScripts();
            $scriptsShell = $blueprintOutput->getScriptsShellScript();
        @endphp

        {{-- Agent Context Section (collapsible) --}}
        @if($agentMd)
            <div x-data="{ open: true, agentContent: @json($agentMd), segments: @json($segments) }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
                <div class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <button type="button" @click="open = !open" class="flex items-center space-x-3 flex-1 text-left">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.agent_context') }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">{{ __('blueprint.agent_md_badge') }}</span>
                    </button>
                    <div class="flex items-center space-x-3">
                        <button type="button" @click="$downloadTextFile(agentContent, '.agent.md')" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors" title="{{ __('blueprint.download_agent_md') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('blueprint.download_agent_md') }}
                        </button>
                        <livewire:shared.copy-to-clipboard
                            :text="$agentMd"
                            :label="__('blueprint.copy_button')"
                            :success-message="__('blueprint.agent_md_copied')"
                        />
                        <button type="button" @click="open = !open" class="p-1">
                            <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-sm text-gray-700 dark:text-gray-200 whitespace-pre-wrap font-mono">{{ $agentMd }}</pre>
                    </div>

                    {{-- Per-segment downloads --}}
                    @if(count($segments) > 0)
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('blueprint.segment_download_title') }}</h3>
                            <div class="space-y-2">
                                @foreach($segments as $segment)
                                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/30 rounded-lg px-3 py-2">
                                        <span class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ $segment['name'] }}</span>
                                        <button type="button" @click="$downloadTextFile(segments[{{ $loop->index }}].content, segments[{{ $loop->index }}].filename)" class="inline-flex items-center px-2.5 py-1 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ __('blueprint.download_segment') }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- VSCode Extensions Section (collapsible) --}}
        @if(count($extensions) > 0)
            <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
                <div class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <button type="button" @click="open = !open" class="flex items-center space-x-3 flex-1 text-left">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.vscode_extensions') }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">{{ count($extensions) }}</span>
                    </button>
                    <div class="flex items-center space-x-3">
                        <livewire:shared.copy-to-clipboard
                            :text="$installCommand"
                            :label="__('blueprint.copy_install_command')"
                            :success-message="__('blueprint.command_copied')"
                        />
                        <button type="button" @click="open = !open" class="p-1">
                            <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
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
            </div>
        @endif

        {{-- Scripts Section (collapsible) --}}
        @if(!empty($scripts))
            <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
                <div class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <button type="button" @click="open = !open" class="flex items-center space-x-3 flex-1 text-left">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.scripts_section') }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">{{ count($scripts) }}</span>
                    </button>
                    <div class="flex items-center space-x-3">
                        <livewire:shared.copy-to-clipboard
                            :text="$scriptsShell"
                            :label="__('blueprint.copy_scripts_command')"
                            :success-message="__('blueprint.scripts_copied')"
                        />
                        <button type="button" @click="open = !open" class="p-1">
                            <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
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
            </div>
        @endif

        {{-- MCP Servers Section (collapsible) --}}
        @if(!empty($mcpServers))
            <div x-data="{ open: true }" class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6 overflow-hidden">
                <button type="button" @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-center space-x-3">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.mcp_servers') }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200">{{ count($mcpServers['mcp_servers'] ?? []) }}</span>
                    </div>
                    <svg :class="{'rotate-180': !open}" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="px-6 pb-6">
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
        @endif
    </div>
@endsection
