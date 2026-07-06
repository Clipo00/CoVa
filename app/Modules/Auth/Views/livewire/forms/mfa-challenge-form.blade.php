<div>
    <form wire:submit="submit" class="space-y-6">
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('auth.mfa_code_label') }}
            </label>
            <div class="mt-1">
                <input
                    wire:model.live="code"
                    id="code"
                    name="code"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="6"
                    autocomplete="one-time-code"
                    required
                    placeholder="{{ __('auth.mfa_code_placeholder') }}"
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center text-2xl tracking-widest @error('code') border-red-500 @enderror"
                >
            </div>
            @error('code')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('auth.mfa_verify_button') }}
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <button type="button" wire:click="resend"
            class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 hover:underline focus:outline-none">
            {{ __('auth.mfa_resend_button') }}
        </button>
    </div>
</div>
