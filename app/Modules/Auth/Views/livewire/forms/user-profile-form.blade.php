<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('auth.edit_profile') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('auth.update_profile_desc') }}</p>
        </div>

        <form wire:submit="submit" class="px-6 py-6 space-y-6">
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
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
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
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                @error('email') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>

            <!-- Password Change -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('auth.change_password') }}</h3>
                <div class="space-y-4">
                    <div>
                        <label for="currentPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.current_password') }}</label>
                        <input
                            type="password"
                            wire:model="currentPassword"
                            id="currentPassword"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('currentPassword') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="newPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.new_password') }}</label>
                        <input
                            type="password"
                            wire:model="newPassword"
                            id="newPassword"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('newPassword') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="newPasswordConfirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('auth.new_password_confirm') }}</label>
                        <input
                            type="password"
                            wire:model="newPasswordConfirmation"
                            id="newPasswordConfirmation"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                    </div>
                </div>
            </div>

            <!-- Submit -->
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
