<div>
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
        {{ __('auth.mfa_setup_description') }}
    </p>

    <div class="flex gap-3">
        <button
            type="button"
            wire:click="enable"
            class="flex-1 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
        >
            {{ __('auth.mfa_setup_enable_button') }}
        </button>

        <button
            type="button"
            wire:click="skip"
            class="flex-1 inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
        >
            {{ __('auth.mfa_setup_later_button') }}
        </button>
    </div>
</div>
