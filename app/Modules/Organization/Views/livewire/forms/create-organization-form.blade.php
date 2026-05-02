<div>
    <form wire:submit="submit" class="space-y-6 bg-white p-6 rounded-lg shadow">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
                Nombre de la organización
            </label>
            <div class="mt-1">
                <input wire:model.live="name" id="name" name="name" type="text" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
            </div>
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">
                Slug (URL)
            </label>
            <div class="mt-1">
                <input wire:model.live="slug" id="slug" name="slug" type="text" required
                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('slug') border-red-500 @enderror">
            </div>
            <p class="mt-1 text-xs text-gray-500">Se genera automáticamente desde el nombre, pero puedes editarlo.</p>
            @error('slug')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <button type="submit"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Crear Organización
            </button>
        </div>
    </form>
</div>
