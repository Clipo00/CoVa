@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $notifications */
    /** @var int $unreadCount */
@endphp

@extends('layouts.app')

@section('title', __('marketplace.notifications_title'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('marketplace.notifications_title') }}
            </h1>
            @if ($unreadCount > 0)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ $unreadCount }} {{ __('marketplace.notifications_empty') ? __('marketplace.notifications_empty') : '' }}
                </p>
            @endif
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}">
                @csrf
                <button type="submit" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors">
                    {{ __('marketplace.notification_mark_all_read') }}
                </button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">
                {{ __('marketplace.notifications_empty') }}
            </p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($notifications as $notification)
                    <div class="px-6 py-4 flex items-start space-x-4 {{ $notification->read_at ? '' : 'bg-indigo-50/50 dark:bg-indigo-900/10' }}">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-0.5">
                            @if ($notification->type === 'blueprint_updated')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            @elseif ($notification->type === 'blueprint_deleted')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </div>

                        <!-- Content -->
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

                        <!-- Mark as read button -->
                        @unless ($notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="flex-shrink-0">
                                @csrf
                                <button type="submit" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium transition-colors" title="{{ __('marketplace.notification_mark_read') }}">
                                    {{ __('marketplace.notification_mark_read') }}
                                </button>
                            </form>
                        @endunless
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
