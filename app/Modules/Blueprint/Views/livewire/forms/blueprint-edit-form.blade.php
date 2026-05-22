<div>
    <form wire:submit="submit" class="space-y-8">
        {{-- Información básica --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2">{{ __('blueprint.general_info') }}</h2>
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('blueprint.title_label') }}</label>
                <input wire:model.live="title" type="text" id="title" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('blueprint.title_placeholder') }}">
                @error('title') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('blueprint.slug_label') }}</label>
                <input wire:model.live="slug" type="text" id="slug" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.slug_hint') }}</p>
                @error('slug') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="categoryId" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('blueprint.category_label') }}</label>
                <select wire:model="categoryId" id="categoryId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('blueprint.category_none') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('categoryId') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('blueprint.description_label') }}</label>
                <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('blueprint.description_placeholder') }}"></textarea>
                @error('description') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Variables --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            @include('blueprint::livewire.components.variable-manager')
            @error('variables') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
            @foreach($errors->messages() as $key => $messages)
                @if(str_starts_with($key, 'variables.'))
                    @foreach($messages as $message)
                        <span class="text-red-500 text-sm block">{{ $message }}</span>
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Tabs --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">{{ __('blueprint.tabs_section') }}</h2>
            <livewire:blueprint.components.tab-manager
                :tabs-config="$tabsConfig"
            />
        </div>

        {{-- Submit --}}
        <div class="flex justify-between items-center">
            <a href="{{ route('blueprints.show', $blueprint->uuid) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                {{ __('blueprint.cancel_link') }}
            </a>
            <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                </svg>
                {{ __('blueprint.edit_blueprint_button') }}
            </button>
        </div>
    </form>
</div>