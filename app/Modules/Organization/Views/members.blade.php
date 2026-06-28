@extends('layouts.app')

@section('title', __('organization.members_title', ['name' => $organization->name]))

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Breadcrumb --}}
        <div class="mb-6 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ __('layouts.dashboard') }}</a>
            <span>/</span>
            <a href="{{ route('organizations.show', $organization->slug) }}" class="hover:text-gray-700 dark:hover:text-gray-200">{{ $organization->name }}</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-gray-100">{{ __('organization.members_breadcrumb') }}</span>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('organization.members_heading') }}</h1>
            <span class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                {{ __('organization.member_count', ['count' => $organization->members->count()]) }}
            </span>
        </div>

        @can('invite', $organization)
            {{-- Create Member Direct Form --}}
            <div class=" bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6 border-l-4 border-indigo-500">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('organization.direct_member_heading') }}</h2>
                <form method="POST" action="{{ route('organizations.members.store', $organization->slug) }}" class="flex flex-col sm:flex-row gap-4 items-end">
                    @csrf
                    <div class="flex-1">
                        <label for="name" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('organization.member_name_label') }}</label>
                        <input type="text" name="name" id="name" placeholder="{{ __('organization.member_name_placeholder') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex-1">
                        <label for="email" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('organization.member_email_label') }}</label>
                        <input type="email" name="email" id="email" placeholder="{{ __('organization.member_email_placeholder') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="w-full sm:w-40">
                        <label for="role" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('organization.member_role_label') }}</label>
                        <select name="role" id="role" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="developer">{{ __('organization.role_developer') }}</option>
                            <option value="maintainer">{{ __('organization.role_maintainer') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('organization.create_member_button') }}
                    </button>
                </form>
            </div>

            {{-- Invite Form --}}
            <div class=" bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('organization.invite_heading') }}</h2>
                <form method="POST" action="{{ route('organizations.invite', $organization->slug) }}" class="flex flex-col sm:flex-row gap-4">
                    @csrf
                    <div class="flex-1">
                        <input type="email" name="email" placeholder="{{ __('organization.invite_email_placeholder') }}" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="w-full sm:w-48">
                        <select name="role" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="developer">{{ __('organization.role_developer') }}</option>
                            <option value="maintainer">{{ __('organization.role_maintainer') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        {{ __('organization.invite_button') }}
                    </button>
                </form>
            </div>
        @endcan

        {{-- Members List --}}
        <div class=" bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md mb-6">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($organization->members as $member)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center min-w-0">
                                <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-sm">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <div class="ml-4 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $member->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $member->email }}</p>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex items-center space-x-3">
                                @if($member->id === $organization->owner_id)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200">
                                        {{ __('organization.role_owner') }}
                                    </span>
                                @else
                                    @can('manageMembers', $organization)
                                        <form method="POST" action="{{ route('organizations.members.role', [$organization->slug, $member->id]) }}" class="flex items-center space-x-2">
                                            @csrf
                                            <select name="role" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs py-1">
                                                <option value="developer" {{ $member->pivot->role === 'developer' ? 'selected' : '' }}>{{ __('organization.role_developer') }}</option>
                                                <option value="maintainer" {{ $member->pivot->role === 'maintainer' ? 'selected' : '' }}>{{ __('organization.role_maintainer') }}</option>
                                            </select>
                                        </form>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $member->pivot->role === 'maintainer' ? 'bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200' : 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' }}">
                                            {{ __('organization.role_' . (in_array($member->pivot->role, ['developer', 'maintainer', 'owner']) ? $member->pivot->role : 'developer')) }}
                                        </span>
                                    @endcan
                                    @can('removeMember', $organization)
                                        @if($member->id !== auth()->id())
                                            <form method="POST" action="{{ route('organizations.members.remove', [$organization->slug, $member->id]) }}" x-data class="inline" @submit.prevent="const f=$el; $store.confirm.ask({message:'{{ __('organization.remove_member_confirm', ['name' => $member->name]) }}', onConfirm(){ f.submit(); }})">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                    {{ __('organization.remove_member_button') }}
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Pending Invitations --}}
        @php
            $pendingInvitations = $organization->invitations->whereNull('used_at')->where('expires_at', '>', now());
        @endphp
        @if($pendingInvitations->count() > 0)
            <div class=" bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('organization.pending_invitations') }}</h3>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($pendingInvitations as $invitation)
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $invitation->email }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('organization.invitation_info', ['role' => __('organization.role_' . (in_array($invitation->role, ['developer', 'maintainer', 'owner']) ? $invitation->role : 'developer')), 'time' => $invitation->expires_at->diffForHumans()]) }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200">
                                    {{ __('organization.status_pending') }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection