<div>
    {{-- Search and Sort --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('marketplace.search_placeholder') }}"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
        </div>
        <div class="sm:w-48">
            <select
                wire:model.live="sort"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
                <option value="recent">{{ __('marketplace.sort_recent') }}</option>
                <option value="rating">{{ __('marketplace.sort_rating') }}</option>
                <option value="subscribers">{{ __('marketplace.sort_subscribers') }}</option>
            </select>
        </div>
    </div>

    {{-- Tag Filter --}}
    @if($availableTags->isNotEmpty())
        <div class="mb-6">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-2">{{ __('marketplace.tag_filter') }}:</span>
            <div class="inline-flex flex-wrap gap-2 mt-1">
                @foreach($availableTags as $tag)
                    <button
                        wire:click="toggleTag('{{ $tag }}')"
                        class="px-3 py-1 text-sm rounded-full transition-colors duration-150 {{ in_array($tag, $selectedTags) ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                    >
                        {{ $tag }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Blueprint Grid --}}
    @if($blueprints->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <div class="mx-auto h-16 w-16 text-gray-400 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('marketplace.empty_heading') }}</h3>
            <p class="text-gray-600 dark:text-gray-300 mb-6 max-w-md mx-auto">
                {{ __('marketplace.empty_description') }}
            </p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('marketplace.empty_cta') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($blueprints as $blueprint)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $blueprint->title }}
                        </h3>
                    </div>

                    @if($blueprint->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                            {{ $blueprint->description }}
                        </p>
                    @endif

                    {{-- Tags --}}
                    @if($blueprint->tags->isNotEmpty())
                        <div class="flex flex-wrap gap-1 mb-4">
                            @foreach($blueprint->tags as $tag)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Stats --}}
                    <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex items-center space-x-4">
                            <span title="{{ __('marketplace.votes_count') }}">
                                <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                                {{ $blueprint->votes_count }}
                            </span>
                            <span title="{{ __('marketplace.subscribers_count') }}">
                                <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                {{ $blueprint->subscribers_count }}
                            </span>
                        </div>
                        @if($blueprint->organization)
                            <span class="text-xs">
                                {{ __('marketplace.by_org') }} {{ $blueprint->organization->name }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $blueprints->links() }}
        </div>
    @endif
</div>
