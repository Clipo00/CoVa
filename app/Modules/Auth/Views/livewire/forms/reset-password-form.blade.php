<div>
    <form wire:submit="resetPassword" class="space-y-6">
        <input type="hidden" wire:model="token" name="token">
        <input type="hidden" wire:model="email" name="email">

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('auth.email_label') }}
            </label>
            <div class="mt-1">
                <input wire:model.live="email" id="email" name="email" type="email" readonly
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-gray-100 dark:bg-gray-600 @error('email') border-red-500 @enderror">
            </div>
            @error('email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('auth.password_reset_new_password') }}
            </label>
            <div class="mt-1">
                <input wire:model.live="password" id="password" name="password" type="password" autocomplete="new-password" required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-500 @enderror">
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="passwordConfirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('auth.password_reset_new_password_confirm') }}
            </label>
            <div class="mt-1">
                <input wire:model.live="password_confirmation" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
        </div>

        <div>
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('auth.password_reset_submit') }}
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
            {{ __('auth.login_link') }}
        </a>
    </div>
</div>
