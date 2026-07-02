<div>
    <form wire:submit="submit" class="space-y-6">
        {{-- Información básica --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 overflow-hidden">
            <div class="px-5 py-3 bg-indigo-50/50 dark:bg-indigo-900/20 border-b border-indigo-100 dark:border-indigo-800/30">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.general_info') }}</h2>
                </div>
            </div>
            
            <div class="p-6 space-y-5">
                {{-- Title & Slug in 2 columns --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('blueprint.title_label') }}</label>
                        <input wire:model.live="title" type="text" id="title" 
                            class="block w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/30 transition-all" 
                            placeholder="{{ __('blueprint.title_placeholder') }}">
                        @error('title') <span class="text-red-500 text-sm mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('blueprint.slug_label') }}</label>
                        <div class="relative">
                            <input wire:model.live="slug" type="text" id="slug" 
                                class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-mono text-gray-600 dark:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/30 transition-all">
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.slug_hint') }}</p>
                        @error('slug') <span class="text-red-500 text-sm mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('blueprint.description_label') }}</label>
                    <textarea wire:model="description" id="description" rows="3" 
                        class="block w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/30 transition-all resize-none" 
                        placeholder="{{ __('blueprint.description_placeholder') }}"></textarea>
                    @error('description') <span class="text-red-500 text-sm mt-1.5">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Tags --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 overflow-hidden">
            <div class="px-5 py-3 bg-purple-50/50 dark:bg-purple-900/20 border-b border-purple-100 dark:border-purple-800/30">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.tags_section') }}</h2>
                    <span class="ml-auto text-xs text-gray-400">{{ __('blueprint.tags_hint') }}</span>
                </div>
            </div>
            <div class="p-5">
                <div class="flex flex-wrap gap-2">
                    @foreach($allTags as $tag)
                        <label class="cursor-pointer">
                            <input type="checkbox" wire:model="selectedTags" value="{{ $tag->id }}" class="sr-only peer">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium border transition-all
                                peer-checked:bg-purple-100 peer-checked:text-purple-700 peer-checked:border-purple-300
                                dark:peer-checked:bg-purple-900/40 dark:peer-checked:text-purple-300 dark:peer-checked:border-purple-700
                                bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-600
                                hover:border-purple-300 dark:hover:border-purple-700">
                                {{ $tag->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('selectedTags') <span class="text-red-500 text-sm mt-2 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Publish Toggle --}}
        @can('publish', $blueprint)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 overflow-hidden">
                <div class="px-5 py-3 bg-rose-50/50 dark:bg-rose-900/20 border-b border-rose-100 dark:border-rose-800/30">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/40 flex items-center justify-center">
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.publish_section') }}</h2>
                    </div>
                </div>
                <div class="p-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="isPublic" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('blueprint.publish_toggle') }}</span>
                    </label>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.publish_help') }}</p>
                    @error('isPublic') <span class="text-red-500 text-sm mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                </div>
            </div>
        @endcan

        {{-- Variables --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 overflow-hidden">
            <div class="px-5 py-3 bg-emerald-50/50 dark:bg-emerald-900/20 border-b border-emerald-100 dark:border-emerald-800/30">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.env_variables') }}</h2>
                </div>
            </div>
            <div class="p-6">
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
        </div>

        {{-- Tabs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 overflow-hidden">
            <div class="px-5 py-3 bg-amber-50/50 dark:bg-amber-900/20 border-b border-amber-100 dark:border-amber-800/30">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('blueprint.tabs_section') }}</h2>
                </div>
            </div>
            <div class="p-6">
                <livewire:blueprint.components.tab-manager
                    :tabs="$tabsConfig"
                    wire:key="edit-tab-manager"
                />
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-between items-center pt-2">
            <a href="{{ route('blueprints.show', $blueprint->slug) }}" 
                class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('blueprint.cancel_link') }}
            </a>
            <button type="submit" 
                class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                </svg>
                {{ __('blueprint.edit_blueprint_button') }}
            </button>
        </div>
    </form>

    {{-- Live Preview Panel --}}
</div>
