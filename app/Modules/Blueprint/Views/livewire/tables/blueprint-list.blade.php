<div
    x-data="{
        filterKey: 'blueprint_filters_{{ auth()->id() }}',
        init() {
            const saved = localStorage.getItem(this.filterKey);
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    if (data.preserveFilters) {
                        // Livewire needs to know: restore filters AND preserve setting
                        $wire.set('preserveFilters', true).then(() => {
                            $wire.set('filters', data.filters);
                        });
                    }
                } catch (e) {}
            }
        },
        persistFilters() {
            localStorage.setItem(this.filterKey, JSON.stringify({
                filters: $wire.filters,
                preserveFilters: $wire.preserveFilters
            }));
        }
    }"
    x-on:persist-filters.window="persistFilters()"
>
    {{-- Search + Filter Button --}}
    <div class="flex items-center gap-3 mb-4">
        {{-- Search Input --}}
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
            <input
                wire:model.live="search"
                type="text"
                placeholder="{{ __('blueprint.search_placeholder') }}"
                class="block w-full pl-10 pr-3 py-2 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                aria-label="{{ __('blueprint.search_placeholder') }}"
            >
        </div>

        {{-- Filter Toggle Button --}}
        <div x-data="{ open: @entangle('showFilters') }" class="relative">
            <button
                type="button"
                @click="open = !open"
                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                :aria-expanded="open"
                aria-controls="blueprint-filters-panel"
                aria-label="{{ __('blueprint.filter_button') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                <span class="hidden sm:inline">{{ __('blueprint.filter_button') }}</span>

                {{-- Active filter badge --}}
                @if($activeFilterCount > 0)
                    <span class="ml-1.5 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-indigo-500 rounded-full">
                        {{ $activeFilterCount }}
                    </span>
                @endif
            </button>

            {{-- Filter Dropdown --}}
            <div
                id="blueprint-filters-panel"
                x-show="open"
                @click.away="open = false"
                @keydown.escape.window="open = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-20"
                role="region"
                aria-label="{{ __('blueprint.filter_button') }}"
                x-cloak
            >
                {{-- Organizations section --}}
                <div class="px-3 py-2">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                        {{ __('blueprint.filter_organizations') }}
                    </p>
                    @forelse($this->userOrganizations as $org)
                        <label class="flex items-center py-1 px-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                value="{{ $org->id }}"
                                wire:model.live="filters.organizations"
                                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-200 truncate">{{ $org->name }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-gray-400 italic py-1">{{ __('shared.no_results') }}</p>
                    @endforelse
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                {{-- Categories section --}}
                <div class="px-3 py-2">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                        {{ __('blueprint.filter_categories') }}
                    </p>
                    @forelse($this->categories as $category)
                        <label class="flex items-center py-1 px-1 rounded hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                            <input
                                type="checkbox"
                                value="{{ $category->id }}"
                                wire:model.live="filters.categories"
                                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            >
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-200 truncate">{{ $category->name }}</span>
                        </label>
                    @empty
                        <p class="text-xs text-gray-400 italic py-1">{{ __('shared.no_results') }}</p>
                    @endforelse
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                {{-- Preserve + Marketplace footer --}}
                <div class="px-3 py-2 space-y-2">
                    <label class="flex items-center py-1 cursor-pointer transition-colors" title="{{ __('blueprint.filter_preserve_hint') }}">
                        <input
                            type="checkbox"
                            wire:model.live="preserveFilters"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        >
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">{{ __('blueprint.filter_preserve') }}</span>
                    </label>
                    @if(config('marketplace.enabled'))
                        <p class="text-xs text-gray-400 dark:text-gray-500 italic">
                            🚧 {{ __('blueprint.filter_marketplace') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Active filter tags --}}
    @if($activeFilterCount > 0)
        <div class="flex flex-wrap items-center gap-2 mb-4" role="status" aria-live="polite">
            @foreach($filters['organizations'] as $orgId)
                @php $org = $this->userOrganizations->firstWhere('id', $orgId); @endphp
                @if($org)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-200">
                        {{ __('blueprint.filter_tag_org', ['name' => $org->name]) }}
                        <button
                            wire:click="removeFilter('organizations', {{ $orgId }})"
                            class="inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-indigo-200 dark:hover:bg-indigo-700 transition-colors"
                            aria-label="{{ __('shared.remove_filter', ['filter' => $org->name]) }}"
                        >
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </span>
                @endif
            @endforeach

            @foreach($filters['categories'] as $catId)
                @php $cat = $this->categories->firstWhere('id', $catId); @endphp
                @if($cat)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">
                        {{ __('blueprint.filter_tag_cat', ['name' => $cat->name]) }}
                        <button
                            wire:click="removeFilter('categories', {{ $catId }})"
                            class="inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-green-200 dark:hover:bg-green-700 transition-colors"
                            aria-label="{{ __('shared.remove_filter', ['filter' => $cat->name]) }}"
                        >
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </span>
                @endif
            @endforeach

            <button
                wire:click="clearFilters"
                class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 underline transition-colors"
            >
                {{ __('blueprint.filter_clear_all') }}
            </button>
        </div>
    @endif

    {{-- Results --}}
    @if($blueprints->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            @if($activeFilterCount > 0 || $search)
                {{-- No results with active filters --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-3" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                </svg>
                <p>{{ __('blueprint.list_empty_filtered') }}</p>
            @else
                {{-- No results at all --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p>
                    {{ __('blueprint.list_empty') }}
                    <a href="{{ route('blueprints.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                        {{ __('blueprint.list_empty_link') }}
                    </a>
                </p>
            @endif
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($blueprints as $blueprint)
                    <li class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                            <a href="{{ route('blueprints.show', $blueprint->slug) }}" class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">{{ $blueprint->title }}</p>
                                    <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                        @if($blueprint->is_public)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200">
                                                {{ __('blueprint.badge_public') }}
                                            </span>
                                        @endif
                                        @if($blueprint->category)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
                                                {{ $blueprint->category->name }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $blueprint->organization->name }}</span>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 truncate">{{ $blueprint->description }}</p>
                            </a>
                            @can('delete', $blueprint)
                                <form method="POST" action="{{ route('blueprints.destroy', $blueprint->uuid) }}" x-data class="ml-4 flex-shrink-0" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ __('blueprint.delete_confirm') }}', onConfirm(){ f.submit(); }})">
                                    @csrf
                                    <button type="submit" class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-300 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20" title="{{ __('blueprint.delete_tooltip') }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
