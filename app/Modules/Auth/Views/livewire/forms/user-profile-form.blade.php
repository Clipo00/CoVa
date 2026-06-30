<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('auth.edit_profile') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('auth.update_profile_desc') }}</p>
        </div>

        <form wire:submit="submit" class="px-6 py-6 space-y-6">
            <!-- Datos Tab: Avatar, Name, Email -->
            <div x-show="activeTab === 'datos'" x-cloak class="space-y-6">
            <!-- Avatar -->
            <div class="flex items-center space-x-6">
                <div class="relative">
                    @if($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" class="h-20 w-20 rounded-full object-cover border-2 border-indigo-500">
                    @elseif(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatarUrl() }}" class="h-20 w-20 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700">
                    @else
                        <div class="h-20 w-20 rounded-full bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.profile_photo') }}</label>
                    <input
                        type="file"
                        wire:model="avatar"
                        accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:bg-indigo-900/30 file:text-indigo-700 dark:text-indigo-300 hover:file:bg-indigo-100"
                    >
                    @error('avatar') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('auth.profile_photo_hint') }}</p>
                </div>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.name_label') }}</label>
                <input
                    type="text"
                    wire:model="name"
                    id="name"
                    class="mt-1 block w-full py-2.5 px-3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                @error('name') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.email_label') }}</label>
                <input
                    type="email"
                    wire:model="email"
                    id="email"
                    class="mt-1 block w-full py-2.5 px-3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                >
                @error('email') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            </div>

            <!-- Cuenta Tab: Password Change + MFA -->
            <div x-show="activeTab === 'cuenta'" x-cloak class="space-y-6">
            <!-- Password Change -->
            <div class="pt-6">
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('auth.change_password') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label for="currentPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.current_password') }}</label>
                    <div x-data="{ show: false }" class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            wire:model="currentPassword"
                            id="currentPassword"
                            autocomplete="off"
                            readonly
                            x-on:focus="$el.removeAttribute('readonly')"
                            class="mt-1 block w-full py-2.5 px-3 pr-10 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                        <button type="button" x-on:click="show = !show" tabindex="-1"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 dark:text-gray-200 hover:text-gray-600 dark:hover:text-white cursor-pointer">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                        @error('currentPassword') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.new_password') }}</label>
                    <div x-data="{ show: false }" class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            wire:model="newPassword"
                            id="newPassword"
                            class="mt-1 block w-full py-2.5 px-3 pr-10 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                        <button type="button" x-on:click="show = !show" tabindex="-1"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 dark:text-gray-200 hover:text-gray-600 dark:hover:text-white cursor-pointer">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                        @error('newPassword') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="newPasswordConfirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.new_password_confirm') }}</label>
                    <div x-data="{ show: false }" class="relative">
                        <input
                            :type="show ? 'text' : 'password'"
                            wire:model="newPasswordConfirmation"
                            id="newPasswordConfirmation"
                            class="mt-1 block w-full py-2.5 px-3 pr-10 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                        >
                        <button type="button" x-on:click="show = !show" tabindex="-1"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 dark:text-gray-200 hover:text-gray-600 dark:hover:text-white cursor-pointer">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    </div>
                </div>
            </div>

            <!-- MFA Toggle -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ __('auth.mfa_challenge_title') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('auth.mfa_setup_desc') }}
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="mfaEnabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>
            </div>

            <!-- Submit (visible on all tabs) -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="submit"
                    data-testid="profile-submit"
                    wire:loading.attr="disabled"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                    <span wire:loading.remove>{{ __('auth.save_button') }}</span>
                    <span wire:loading>{{ __('auth.saving_button') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
