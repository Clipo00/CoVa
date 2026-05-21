<div>
    <form wire:submit="submit" class="space-y-8">
        {{-- Información básica --}}
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2">Información General</h2>

            {{-- Selector de Organización --}}
            <div>
                <label for="organizationId" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Organización *</label>
                @if($lockOrganization)
                    <div class="mt-1 relative">
                        <input type="text" disabled
                            value="{{ collect($userOrganizations)->firstWhere('id', $organizationId)['name'] ?? '' }}"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 bg-gray-100 text-gray-500 dark:text-gray-400 shadow-sm cursor-not-allowed"
                        >
                        <input type="hidden" wire:model="organizationId">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Organización preseleccionada desde la página anterior.</p>
                @else
                    <select wire:model="organizationId" id="organizationId"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($userOrganizations as $org)
                            @if($org['hasAvailableSlots'])
                                <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                @endif
                @error('organizationId') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Título *</label>
                <input wire:model.live="title" type="text" id="title" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Mi Proyecto Laravel">
                @error('title') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Slug *</label>
                <input wire:model.live="slug" type="text" id="slug" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Identificador único para URLs. Se genera automáticamente desde el título.</p>
                @error('slug') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="categoryId" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Categoría</label>
                <select wire:model="categoryId" id="categoryId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Sin categoría</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('categoryId') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Descripción</label>
                <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Describe el propósito de este blueprint..."></textarea>
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
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b pb-2 mb-4">Pestañas</h2>
            <livewire:blueprint.components.tab-manager
                :tabs-config="$tabsConfig"
                wire:key="create-tab-manager"
            />
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Crear Blueprint
            </button>
        </div>
    </form>
</div>
