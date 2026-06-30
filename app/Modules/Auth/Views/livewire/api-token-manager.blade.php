<div>
    {{-- Token List --}}
    @if($tokens->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('auth.token_name') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('auth.token_last_used') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('auth.token_expires_at') }}
                        </th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ __('auth.token_actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($tokens as $token)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $token->name }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : __('auth.token_last_used_never') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                @if($token->expires_at)
                                    {{ $token->expires_at->format(__('auth.date_format')) }}
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <button
                                    wire:click="confirmRevoke({{ $token->id }})"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium transition-colors duration-150"
                                >
                                    {{ __('auth.token_revoke') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        {{-- Empty state --}}
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('auth.token_empty') }}</p>
        </div>
    @endif

    {{-- Create Token Section --}}
    @if($isFreePlan)
        {{-- Free Plan CTA --}}
        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-start space-x-3">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">{{ __('auth.token_plan_cta') }}</p>
                    <a href="{{ route('pricing') }}" class="mt-2 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                        {{ __('auth.token_upgrade_link') }}
                        <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @else
        {{-- Create Form Toggle --}}
        <div class="mt-6">
            <button
                wire:click="$set('showCreateForm', {{ $showCreateForm ? 'false' : 'true' }})"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors duration-150"
            >
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('auth.token_create') }}
            </button>
        </div>

        {{-- Create Form --}}
        @if($showCreateForm)
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                <form wire:submit="createToken" class="space-y-4">
                    {{-- Token Name --}}
                    <div>
                        <label for="tokenName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.token_name') }}
                        </label>
                        <input
                            id="tokenName"
                            type="text"
                            wire:model="tokenName"
                            maxlength="255"
                            class="mt-1 block w-full py-2.5 px-3 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm"
                        />
                        @error('tokenName')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Organization Selector (only if multiple eligible orgs) --}}
                    @if ($eligibleOrganizations->count() > 1)
                    <div>
                        <label for="selectedOrganizationId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.token_organization') }}
                        </label>
                        <select
                            id="selectedOrganizationId"
                            wire:model="selectedOrganizationId"
                            class="mt-1 block w-full py-2.5 px-3 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm"
                        >
                            <option value="">{{ __('auth.token_select_org') }}</option>
                            @foreach ($eligibleOrganizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedOrganizationId')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    {{-- Expiration Date --}}
                    <div>
                        <label for="expiresAt" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.token_expires_at') }}
                        </label>
                        <input
                            id="expiresAt"
                            type="date"
                            wire:model="expiresAt"
                            min="{{ now()->format('Y-m-d') }}"
                            max="{{ now()->addYear()->format('Y-m-d') }}"
                            class="mt-1 block w-full py-2.5 px-3 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm"
                        />
                        @error('expiresAt')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Current Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.current_password') }}
                        </label>
                        <input
                            id="password"
                            type="password"
                            wire:model="password"
                            class="mt-1 block w-full py-2.5 px-3 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm"
                        />
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors duration-150"
                        >
                            {{ __('auth.token_create_button') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif

    {{-- One-time Token Display --}}
    @if($newPlainTextToken)
        <div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700">
            <div class="flex items-start space-x-3">
                <svg class="h-5 w-5 text-yellow-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ __('auth.token_one_time_warning') }}
                    </p>
                    <div class="mt-2" x-data="{ showToken: false }">
                        <div class="flex items-center space-x-2">
                            <code
                                x-show="showToken"
                                class="flex-1 block p-2 bg-white dark:bg-gray-800 rounded text-sm font-mono text-gray-900 dark:text-gray-100 border border-yellow-300 dark:border-yellow-600 break-all"
                            >{{ $this->tokenWithoutPrefix() }}</code>
                            <code
                                x-show="!showToken"
                                class="flex-1 block p-2 bg-white dark:bg-gray-800 rounded text-sm font-mono text-gray-400 dark:text-gray-500 border border-yellow-300 dark:border-yellow-600"
                            >••••••••••••••••••••••••••••••••</code>
                            <button type="button" x-on:click="showToken = !showToken" tabindex="-1"
                                class="flex-shrink-0 inline-flex items-center px-3 py-2 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-lg hover:bg-yellow-200 dark:text-yellow-200 dark:bg-yellow-800 dark:hover:bg-yellow-700 transition-colors duration-150"
                                title="{{ __('auth.token_show_hide') }}">
                                <svg x-show="!showToken" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showToken" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                            <button
                                wire:click="$dispatch('copy-to-clipboard', { text: '{{ $this->tokenWithoutPrefix() }}' })"
                                class="flex-shrink-0 inline-flex items-center px-3 py-2 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-lg hover:bg-yellow-200 dark:text-yellow-200 dark:bg-yellow-800 dark:hover:bg-yellow-700 transition-colors duration-150"
                                title="{{ __('auth.token_copy') }}"
                            >
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                </svg>
                                {{ __('auth.token_copy') }}
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button
                            wire:click="dismissNewToken"
                            class="text-sm font-medium text-yellow-800 hover:text-yellow-600 dark:text-yellow-200 dark:hover:text-yellow-300 transition-colors duration-150"
                        >
                            {{ __('auth.token_dismiss') }} &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Revoke Confirmation Dialog --}}
    @if($revokeTokenId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('auth.token_revoke_confirm_title') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('auth.token_revoke_confirm') }}
                </p>

                <form wire:submit="revokeToken" class="space-y-4">
                    <div>
                        <label for="revokePassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('auth.current_password') }}
                        </label>
                        <input
                            id="revokePassword"
                            type="password"
                            wire:model="revokePassword"
                            class="mt-1 block w-full py-2.5 px-3 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:text-gray-100 sm:text-sm"
                        />
                        @error('revokePassword')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            type="button"
                            wire:click="cancelRevoke"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors duration-150"
                        >
                            {{ __('auth.cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 transition-colors duration-150"
                        >
                            {{ __('auth.token_revoke_confirm_button') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
