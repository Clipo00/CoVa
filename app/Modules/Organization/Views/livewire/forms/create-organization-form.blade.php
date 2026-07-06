<div>
    <form wire:submit="submit" class="space-y-6 bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('organization.name_label') }}
            </label>
            <div class="mt-1">
                <input wire:model.live="name" id="name" name="name" type="text" required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
            </div>
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('organization.slug_label') }}
            </label>
            <div class="mt-1">
                <input wire:model.live="slug" id="slug" name="slug" type="text" required
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('slug') border-red-500 @enderror">
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('organization.slug_hint') }}</p>
            @error('slug')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('organization.create_button') }}
            </button>
        </div>
    </form>
</div>
