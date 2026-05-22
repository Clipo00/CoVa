<div>
    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="{{ __('blueprint.search_placeholder') }}" class="w-full max-w-md rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    @if($blueprints->isEmpty())
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            {{ __('blueprint.list_empty') }} <a href="{{ route('blueprints.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">{{ __('blueprint.list_empty_link') }}</a>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($blueprints as $blueprint)
                    <li class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                            <a href="{{ route('blueprints.show', $blueprint->uuid) }}" class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">{{ $blueprint->title }}</p>
                                    <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
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