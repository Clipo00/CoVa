@extends('layouts.landing')

@section('title', $blueprint->title)

@section('content')
    <div class="pt-24 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumb --}}
            <div class="mb-6 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('marketplace.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    {{ __('marketplace.marketplace_title') }}
                </a>
                <span>/</span>
                <span class="text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</span>
            </div>

            {{-- Header Card --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between">
                    {{-- Left: Title, meta --}}
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $blueprint->title }}</h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">
                                <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ __('marketplace.public_badge') }}
                            </span>
                        </div>

                        @if($blueprint->category)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">
                                {{ $blueprint->category->name }}
                            </span>
                        @endif

                        @if($blueprint->description)
                            <p class="mt-4 text-gray-600 dark:text-gray-300">{{ $blueprint->description }}</p>
                        @endif

                        {{-- Author and stats --}}
                        <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <span>
                                {{ __('marketplace.by_org') }}
                                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $blueprint->organization->name }}</span>
                            </span>

                            @auth
                                <span class="flex items-center gap-1"
                                    x-data="{
                                        votesCount: {{ $blueprint->votes_count }},
                                        userVote: {{ json_encode($userVote) }},
                                        loading: false,
                                        async vote(value) {
                                            if (this.loading) return;
                                            this.loading = true;

                                            // Optimistic update
                                            const oldVote = this.userVote;
                                            if (this.userVote === value) {
                                                // Toggle off
                                                this.votesCount -= value;
                                                this.userVote = null;
                                            } else if (this.userVote === null) {
                                                // New vote
                                                this.votesCount += value;
                                                this.userVote = value;
                                            } else {
                                                // Flip
                                                this.votesCount += value * 2;
                                                this.userVote = value;
                                            }

                                            try {
                                                const response = await fetch('{{ route('marketplace.vote', $blueprint->uuid) }}', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json',
                                                    },
                                                    body: JSON.stringify({ vote: value }),
                                                });

                                                if (!response.ok) throw new Error('Vote failed');

                                                const data = await response.json();
                                                this.votesCount = data.votes_count;
                                                this.userVote = data.user_vote;
                                            } catch (e) {
                                                // Revert on failure
                                                this.votesCount = {{ $blueprint->votes_count }};
                                                this.userVote = {{ json_encode($userVote) }};
                                            } finally {
                                                this.loading = false;
                                            }
                                        }
                                    }">
                                    <button type="button"
                                        @click="vote(1)"
                                        :class="userVote === 1 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                        class="transition-colors focus:outline-none"
                                        :title="'{{ __('marketplace.vote_up') }}'"
                                        :disabled="loading">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>

                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 min-w-[2rem] text-center"
                                        x-text="votesCount"></span>

                                    <button type="button"
                                        @click="vote(-1)"
                                        :class="userVote === -1 ? 'text-red-500 dark:text-red-400' : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'"
                                        class="transition-colors focus:outline-none"
                                        :title="'{{ __('marketplace.vote_down') }}'"
                                        :disabled="loading">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </span>
                            @else
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                    <span>{{ $blueprint->votes_count }} {{ __('marketplace.votes_label') }}</span>
                                </span>
                            @endauth

                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                <span>{{ $blueprint->subscribers_count }} {{ __('marketplace.subscribers_label') ?? __('marketplace.subscribers_count') }}</span>
                            </span>
                        </div>
                    </div>

                    {{-- Right: Subscribe button --}}
                    <div class="mt-4 sm:mt-0 sm:ml-6 flex-shrink-0">
                        @auth
                            <form method="POST" action="{{ route('marketplace.subscribe', $blueprint->uuid) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    {{ __('marketplace.subscribe_button') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center px-5 py-2.5 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg shadow-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                {{ __('marketplace.login_to_subscribe') }}
                            </a>
                        @endauth
                    </div>
                </div>

                {{-- Tags --}}
                @if($blueprint->tags->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($blueprint->tags as $tag)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $tag->tag }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Variables Section --}}
            @include('blueprint::partials.variables-list', [
                'variables' => $blueprint->variables,
                'canViewSecrets' => $canViewSecrets,
            ])

            {{-- Resolved Tabs --}}
            @include('blueprint::partials.resolved-tabs', [
                'resolvedTabs' => new \App\Modules\Blueprint\DTOs\ResolvedTabs($blueprintOutput->tabs),
            ])
        </div>
    </div>
@endsection
