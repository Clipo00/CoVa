<div>
    @if($organizations->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400 mb-4">{{ __('organization.list_empty') }}</p>
            <a href="{{ route('organizations.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                {{ __('organization.list_empty_link') }}
            </a>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($organizations as $organization)
                    <li>
                        <a href="{{ route('organizations.show', $organization->slug) }}" class="block hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 truncate">
                                        {{ $organization->name }}
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        @php $role = $organization->pivot->role; @endphp
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $role === 'owner' ? 'bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200' : ($role === 'maintainer' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200' : 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200') }}">
                                            {{ ucfirst($role) }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('organization.slug_prefix') }}{{ $organization->slug }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
