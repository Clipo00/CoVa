<section id="docs" class="py-20 sm:py-28 bg-gray-50 dark:bg-gray-900/50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Section title --}}
        <div class="text-center mb-16" x-data x-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 dark:text-gray-100 reveal">
                {{ __('landing.docs_title') }}
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                {{ __('landing.docs_subtitle') }}
            </p>
        </div>

        {{-- Step 1: Install --}}
        <div class="mb-12" x-data x-reveal>
            <div class="flex items-start gap-4">
                <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-lg font-bold text-indigo-600 dark:text-indigo-400">
                    1
                </span>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        {{ __('landing.docs_step1_title') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('landing.docs_step1_desc') }}
                    </p>
                    <div class="bg-gray-900 dark:bg-gray-800 rounded-xl p-4 overflow-x-auto">
                        <pre class="text-sm font-mono text-gray-300 leading-relaxed"><code><span class="text-green-400"># {{ __('landing.docs_step1_cmd1') }} (Linux/macOS)</span>
curl -L -o covar {{ config('app.cli_download_url') }}

<span class="text-green-400"># {{ __('landing.docs_step1_cmd1') }} (Windows PowerShell)</span>
Invoke-WebRequest -Uri {{ config('app.cli_download_url') }} -OutFile covar

<span class="text-green-400"># {{ __('landing.docs_step1_cmd2') }}</span>
chmod +x covar

<span class="text-green-400"># {{ __('landing.docs_step1_cmd3') }}</span>
sudo mv covar /usr/local/bin/

<span class="text-gray-500"># O ejecútalo directamente con PHP sin moverlo:</span>
<span class="text-cyan-400">$</span> php covar config:set-key &lt;tu-api-key&gt;</code></pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Authenticate --}}
        <div class="mb-12" x-data x-reveal>
            <div class="flex items-start gap-4">
                <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-lg font-bold text-emerald-600 dark:text-emerald-400">
                    2
                </span>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        {{ __('landing.docs_step2_title') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{-- !!! SEGURIDAD: {!! !!} es INTENCIONAL. docs_step2_desc contiene HTML
                             (<code>) controlado por el equipo en lang/. --}}
                        {!! __('landing.docs_step2_desc') !!}
                    </p>
                    <div class="bg-gray-900 dark:bg-gray-800 rounded-xl p-4 overflow-x-auto">
                        <pre class="text-sm font-mono text-gray-300 leading-relaxed"><code><span class="text-cyan-400">$</span> covar config:set-key covar_xxxxxxxxxxxx</code></pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Fetch your first blueprint --}}
        <div class="mb-12" x-data x-reveal>
            <div class="flex items-start gap-4">
                <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-lg font-bold text-amber-600 dark:text-amber-400">
                    3
                </span>
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        {{ __('landing.docs_step3_title') }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ __('landing.docs_step3_desc') }}
                    </p>
                    <div class="bg-gray-900 dark:bg-gray-800 rounded-xl p-4 overflow-x-auto">
                        <pre class="text-sm font-mono text-gray-300 leading-relaxed"><code><span class="text-green-400"># {{ __('landing.docs_step3_cmd1') }}</span>
<span class="text-cyan-400">$</span> covar vault:list

<span class="text-green-400"># {{ __('landing.docs_step3_cmd2') }}</span>
<span class="text-cyan-400">$</span> covar vault:fetch mi-blueprint</code></pre>
                    </div>

                    {{-- Command reference table --}}
                    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200/60 dark:border-gray-700/60">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                            {{ __('landing.docs_commands_title') }}
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="text-left py-2 pr-4 font-medium text-gray-500 dark:text-gray-400">{{ __('landing.docs_col_command') }}</th>
                                        <th class="text-left py-2 font-medium text-gray-500 dark:text-gray-400">{{ __('landing.docs_col_desc') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    <tr>
                                        <td class="py-3 pr-4 font-mono text-indigo-600 dark:text-indigo-400">covar config:set-key &lt;key&gt;</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ __('landing.docs_cmd_set_key') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 pr-4 font-mono text-indigo-600 dark:text-indigo-400">covar vault:list</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ __('landing.docs_cmd_list') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 pr-4 font-mono text-indigo-600 dark:text-indigo-400">covar vault:fetch &lt;slug&gt;</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ __('landing.docs_cmd_fetch') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 pr-4 font-mono text-indigo-600 dark:text-indigo-400">                                covar help</td>
                                        <td class="py-3 text-gray-600 dark:text-gray-400">{{ __('landing.docs_cmd_help') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Security note --}}
        <div class="mt-8 flex items-start gap-3 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl" x-data x-reveal>
            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <p class="text-sm text-indigo-800 dark:text-indigo-300">
                {{ __('landing.docs_security_note') }}
            </p>
        </div>
    </div>
</section>
