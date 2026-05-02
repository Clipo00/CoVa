<div>
    <div class="mb-4">
        <input wire:model.live="search" type="text" placeholder="Buscar blueprints..." class="w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    @if($blueprints->isEmpty())
        <div class="text-center py-12 text-gray-500">
            No hay blueprints. <a href="{{ route('blueprints.create', ['org' => $organizationId]) }}" class="text-indigo-600 hover:text-indigo-800">Crea el primero</a>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($blueprints as $blueprint)
                    <li>
                        <a href="{{ route('blueprints.show', $blueprint->uuid) }}" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-indigo-600 truncate">{{ $blueprint->title }}</p>
                                    @if($blueprint->category)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $blueprint->category->name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-gray-500 truncate">{{ $blueprint->description }}</p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
