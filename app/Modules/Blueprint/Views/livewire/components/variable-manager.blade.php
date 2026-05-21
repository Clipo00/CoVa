<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Variables de Entorno</h3>
        <button type="button" wire:click="addVariable" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900/40 hover:bg-indigo-200 dark:hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Añadir Variable
        </button>
    </div>

    @if($errors->has('variables'))
        <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 p-4">
            <p class="text-sm text-red-700 dark:text-red-300">{{ $errors->first('variables') }}</p>
        </div>
    @endif

    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-1/4">Key</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Grupo</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Tipo</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Valor</th>
                    <th scope="col" class="px-3 py-3.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Interactivo</th>
                    <th scope="col" class="px-3 py-3.5 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">Secreto</th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-16">
                        <span class="sr-only">Acciones</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @foreach($variables as $index => $variable)
                    <tr wire:key="variable-{{ $index }}">
                        <td class="py-3 pl-4 pr-3">
                            <input type="text" wire:model="variables.{{ $index }}.key" placeholder="DB_HOST" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono" required>
                        </td>
                        <td class="px-3 py-3">
                            <input type="text" wire:model="variables.{{ $index }}.section" placeholder=".env" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono text-xs">
                        </td>
                        <td class="px-3 py-3">
                            <select wire:model="variables.{{ $index }}.type" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="fixed">Fijo</option>
                                <option value="empty">Vacío</option>
                            </select>
                        </td>
                        <td class="px-3 py-3">
                            <input type="text" wire:model="variables.{{ $index }}.default_value" placeholder="Valor por defecto" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </td>
                        <td class="px-3 py-3 text-center">
                            <input type="checkbox" wire:model="variables.{{ $index }}.is_interactive" class="h-4 w-4 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded">
                        </td>
                        <td class="px-3 py-3 text-center">
                            <input type="checkbox" wire:model="variables.{{ $index }}.is_secret" class="h-4 w-4 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded">
                        </td>
                        <td class="py-3 pl-3 pr-4 text-right sm:pr-6">
                            <button type="button" wire:click="removeVariable({{ $index }})" class="text-red-600 dark:text-red-400 hover:text-red-900" title="Eliminar variable">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(count($variables) === 0)
        <div class="text-center py-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
            <p>No hay variables configuradas.</p>
            <button type="button" wire:click="addVariable" class="mt-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
                Añadir primera variable
            </button>
        </div>
    @endif
</div>
