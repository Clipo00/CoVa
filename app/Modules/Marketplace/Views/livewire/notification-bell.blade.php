<div
    x-data="{ open: false }"
    @click.away="open = false"
    class="relative"
>
    <!-- Bell button -->
    <button
        type="button"
        @click="open = !open"
        class="relative p-2 rounded-md text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
        aria-label="{{ __('marketplace.notification_bell_label') }}"
    >
        <!-- Bell SVG icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Unread badge -->
        @if ($unreadCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ __('marketplace.notifications_title') }}
            </h3>
            @if ($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors"
                >
                    {{ __('marketplace.notification_mark_all_read') }}
                </button>
            @endif
        </div>

        <!-- Notifications list -->
        <div class="max-h-80 overflow-y-auto">
            @forelse ($latestNotifications as $notification)
                <div
                    wire:click="markAsRead({{ $notification->id }})"
                    @click="open = false"
                    class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors border-b border-gray-100 dark:border-gray-700/50 last:border-b-0 {{ $notification->read_at ? '' : 'bg-indigo-50/50 dark:bg-indigo-900/10' }}"
                >
                    <div class="flex items-start space-x-3">
                        <!-- Icon by type -->
                        <div class="flex-shrink-0 mt-0.5">
                            @if ($notification->type === 'blueprint_updated')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            @elseif ($notification->type === 'blueprint_deleted')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </div>

                        <!-- Message -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                @if ($notification->type === 'blueprint_updated')
                                    <strong class="font-medium text-gray-900 dark:text-gray-100">{{ $notification->data['blueprint_title'] ?? '' }}</strong>
                                    {{ __('marketplace.notification_blueprint_updated') }}
                                @elseif ($notification->type === 'blueprint_deleted')
                                    <strong class="font-medium text-gray-900 dark:text-gray-100">{{ $notification->data['blueprint_title'] ?? '' }}</strong>
                                    {{ __('marketplace.notification_blueprint_deleted') }}
                                @else
                                    <span>{{ $notification->data['message'] ?? '' }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <!-- Unread dot -->
                        @unless ($notification->read_at)
                            <span class="flex-shrink-0 w-2 h-2 bg-indigo-500 rounded-full mt-2"></span>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('marketplace.notifications_empty') }}
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Footer with "View all" link -->
        <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <a
                href="{{ route('notifications.index') }}"
                @click="open = false"
                class="block text-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium py-1 transition-colors"
            >
                {{ __('marketplace.notification_view_all') }}
            </a>
        </div>
    </div>
</div>
