<div>
    <form wire:submit="submit" class="space-y-6">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('auth.email_label') }}
            </label>
            <div class="mt-1">
                <input wire:model.live="email" id="email" name="email" type="email" autocomplete="email" required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-500 @enderror">
            </div>
            @error('email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('auth.password_label') }}
            </label>
            <div class="mt-1">
                <input wire:model.live="password" id="password" name="password" type="password" autocomplete="current-password" required
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-500 @enderror">
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

            <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input wire:model="remember" id="remember" name="remember" type="checkbox"
                    class="h-4 w-4 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded">
                <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                    {{ __('auth.remember_me') }}
                </label>
            </div>

            <div class="text-sm">
                <a href="{{ route('password.request') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('auth.forgot_password_link') }}
                </a>
            </div>
        </div>

        <div>
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('auth.login_button') }}
            </button>
        </div>
    </form>

    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                    {{ __('auth.no_account') }}
                </span>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('register') }}"
                class="w-full flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('auth.create_account_link') }}
            </a>
        </div>
    </div>
</div>
