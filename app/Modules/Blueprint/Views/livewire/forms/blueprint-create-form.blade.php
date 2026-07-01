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
                {{-- Organization Selector --}}
                <div>
                    <label for="organizationId" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">{{ __('blueprint.org_label') }}</label>
                    @if($lockOrganization)
                        <div class="relative">
                            <div class="flex items-center w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-500 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm">{{ collect($userOrganizations)->firstWhere('id', $organizationId)['name'] ?? '' }}</span>
                            </div>
                            <input type="hidden" wire:model="organizationId">
                        </div>
                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            {{ __('blueprint.org_locked_hint') }}
                        </p>
                    @else
                        <div class="relative">
                            <select wire:model="organizationId" id="organizationId"
                                class="block w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/30 transition-all appearance-none cursor-pointer">
                                @foreach($userOrganizations as $org)
                                    @if($org['hasAvailableSlots'])
                                        <option value="{{ $org['id'] }}">{{ $org['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    @endif
                    @error('organizationId') <span class="text-red-500 text-sm mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>{{ $message }}</span> @enderror
                </div>

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
            <div class="p-6 space-y-4">
                {{-- Template selector --}}
                <div>
                    <label for="selectedTemplate" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1.5">
                        {{ __('blueprint.template_label') }}
                    </label>
                    <div class="relative">
                        <select wire:model.live="selectedTemplate" id="selectedTemplate"
                            class="block w-full px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:focus:ring-indigo-500/30 transition-all appearance-none cursor-pointer">
                            <option value="">{{ __('blueprint.template_empty') }}</option>
                            @foreach($templates as $key => $template)
                                <option value="{{ $key }}">{{ $template['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ __('blueprint.template_hint') }}</p>
                </div>

                <div class="relative">
                    <div wire:loading wire:target="selectedTemplate" class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 dark:bg-gray-800/70 rounded-lg">
                        <svg class="animate-spin h-5 w-5 mr-2 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span class="text-sm text-indigo-600 dark:text-indigo-400 ml-2">{{ __('blueprint.template_loading') }}</span>
                    </div>
                    <livewire:blueprint.components.tab-manager
                        :tabs="$tabsConfig"
                        :key="'create-tab-manager-' . md5(json_encode($tabsConfig))"
                    />
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end pt-2">
            <button type="submit" 
                class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-all shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ __('blueprint.create_blueprint_button') }}
            </button>
        </div>
    </form>

    {{-- Live Preview Panel --}}
    <div x-data="{
        timeout: null,
        init() {
            Livewire.on('tabs-updated', (event) => {
                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    Livewire.dispatch('preview-refresh', {
                        tabsConfig: event.tabs,
                        variables: $wire.variables
                    });
                }, 300);
            });
            // Initial preview if tabs already populated (template selection)
            if ($wire.tabsConfig && $wire.tabsConfig.length > 0) {
                Livewire.dispatch('preview-refresh', {
                    tabsConfig: $wire.tabsConfig,
                    variables: $wire.variables
                });
            }
        }
    }" class="mt-6">
        <livewire:blueprint.components.preview-panel :can-view-secrets="$this->isOwner" wire:key="create-preview-panel" />
    </div>
</div>
