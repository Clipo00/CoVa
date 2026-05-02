<div>
    @if($organizations->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500 mb-4">No tienes organizaciones todavía.</p>
            <a href="{{ route('organizations.create') }}" class="text-indigo-600 hover:text-indigo-800">
                Crea tu primera organización
            </a>
        </div>
    @else
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @foreach($organizations as $organization)
                    <li>
                        <a href="{{ route('organizations.show', $organization->slug) }}" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-indigo-600 truncate">
                                        {{ $organization->name }}
                                    </p>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $organization->pivot->role }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500">
                                            slug: {{ $organization->slug }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
