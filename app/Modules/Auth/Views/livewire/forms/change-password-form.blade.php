<div class="w-full max-w-md mx-auto">
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
        {{ __('auth.password_change_required') }}
    </p>

    <form wire:submit="submit" class="space-y-4">
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('auth.new_password') }}
            </label>
            <input
                type="password"
                id="password"
                wire:model="password"
                class="block w-full mt-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                required
                autofocus
            >
            @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('auth.confirm_password') }}
            </label>
            <input
                type="password"
                id="password_confirmation"
                wire:model="password_confirmation"
                class="block w-full mt-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100 sm:text-sm"
                required
            >
        </div>

        <button
            type="submit"
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            {{ __('auth.change_password_button') }}
        </button>
    </form>
</div>
