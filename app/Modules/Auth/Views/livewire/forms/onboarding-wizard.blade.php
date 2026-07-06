<div>
    {{-- Step labels mapped from named translation keys --}}
    @php
        $stepLabels = [
            1 => __('onboarding.step_welcome'),
            2 => __('onboarding.step_organization'),
            3 => __('onboarding.step_blueprint'),
            4 => __('onboarding.step_invite'),
            5 => __('onboarding.step_done'),
        ];
    @endphp

    {{-- Step Indicator Bar --}}
    <div class="mb-8 overflow-x-auto">
        <div class="flex items-center justify-center space-x-1 sm:space-x-2 min-w-max">
            @foreach (range(1, 5) as $stepNumber)
                <div class="flex items-center flex-shrink-0">
                    {{-- Step circle --}}
                    <div @class([
                        'w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs sm:text-sm font-medium transition-colors flex-shrink-0',
                        'bg-indigo-600 text-white' => $step === $stepNumber,
                        'bg-indigo-100 text-indigo-600' => $step > $stepNumber,
                        'bg-gray-200 text-gray-500' => $step < $stepNumber,
                    ])>
                        @if ($step > $stepNumber)
                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            {{ $stepNumber }}
                        @endif
                    </div>

                    {{-- Step label (hidden on small screens) --}}
                    <span @class([
                        'hidden md:inline ml-1 sm:ml-2 text-xs sm:text-sm whitespace-nowrap',
                        'font-medium text-indigo-600' => $step === $stepNumber,
                        'text-gray-500' => $step !== $stepNumber,
                    ])>
                        {{ $stepLabels[$stepNumber] }}
                    </span>

                    {{-- Connector line --}}
                    @if ($stepNumber < 5)
                        <div @class([
                            'w-4 sm:w-8 h-0.5 mx-0.5 sm:mx-1 flex-shrink-0',
                            'bg-indigo-600' => $step > $stepNumber,
                            'bg-gray-200' => $step <= $stepNumber,
                        ])></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Email verification banner (shown on all steps when email is unverified) --}}
    @php $currentUser = auth()->user(); @endphp
    @if ($currentUser && !$currentUser->hasVerifiedEmail())
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <p class="text-sm text-yellow-800">
                    {{ __('onboarding.verify_email_notice') }}
                </p>
            </div>
        </div>
    @endif

    {{-- Step 1: Welcome --}}
    @if ($step === 1)
        <div class="text-center">
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('onboarding.welcome_heading', ['name' => auth()->user()->name]) }}
                </h1>
                <p class="text-gray-600">
                    {{ __('onboarding.welcome_description') }}
                </p>
            </div>
            <button type="button" wire:click="goToStep(2)"
                class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('onboarding.start_button') }}
            </button>
        </div>

    {{-- Step 2: Create Organization (SKIPPABLE) --}}
    @elseif ($step === 2)
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ __('onboarding.org_heading') }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('onboarding.org_description') }}</p>

            <form wire:submit="submitOrg" class="space-y-4">
                <div>
                    <label for="orgName" class="block text-sm font-medium text-gray-700">
                        {{ __('onboarding.org_name_label') }}
                    </label>
                    <div class="mt-1">
                        <input wire:model.live="orgName" id="orgName" name="orgName" type="text" required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('orgName') border-red-500 @enderror"
                            placeholder="{{ __('onboarding.org_name_placeholder') }}">
                    </div>
                    @error('orgName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between">
                    <button type="button" wire:click="skipStep"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('onboarding.skip_button') }}
                    </button>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('onboarding.org_submit_button') }}
                    </button>
                </div>
            </form>
        </div>

    {{-- Step 3: Create Blueprint (SKIPPABLE) --}}
    @elseif ($step === 3)
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ __('onboarding.blueprint_heading') }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('onboarding.blueprint_description') }}</p>

            <form wire:submit="submitBlueprint" class="space-y-4">
                <div>
                    <label for="bpTitle" class="block text-sm font-medium text-gray-700">
                        {{ __('onboarding.bp_title_label') }}
                    </label>
                    <div class="mt-1">
                        <input wire:model.live="bpTitle" id="bpTitle" name="bpTitle" type="text"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('bpTitle') border-red-500 @enderror"
                            placeholder="{{ __('onboarding.bp_title_placeholder') }}">
                    </div>
                    @error('bpTitle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bpDescription" class="block text-sm font-medium text-gray-700">
                        {{ __('onboarding.bp_description_label') }}
                    </label>
                    <div class="mt-1">
                        <textarea wire:model.live="bpDescription" id="bpDescription" name="bpDescription" rows="2"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="{{ __('onboarding.bp_description_placeholder') }}"></textarea>
                    </div>
                </div>

                <div class="flex justify-between">
                    <button type="button" wire:click="skipStep"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('onboarding.skip_button') }}
                    </button>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('onboarding.bp_submit_button') }}
                    </button>
                </div>
            </form>
        </div>

    {{-- Step 4: Invite Members (SKIPPABLE) --}}
    @elseif ($step === 4)
        <div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ __('onboarding.invite_heading') }}</h2>
            <p class="text-sm text-gray-600 mb-6">{{ __('onboarding.invite_description') }}</p>

            @if (session('invite_success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-700">
                    {{ session('invite_success') }}
                </div>
            @endif

            <form wire:submit="submitInvite" class="space-y-4">
                <div>
                    <label for="inviteEmail" class="block text-sm font-medium text-gray-700">
                        {{ __('onboarding.invite_email_label') }}
                    </label>
                    <div class="mt-1">
                        <input wire:model.live="inviteEmail" id="inviteEmail" name="inviteEmail" type="email"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('inviteEmail') border-red-500 @enderror"
                            placeholder="{{ __('onboarding.invite_email_placeholder') }}">
                    </div>
                    @error('inviteEmail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between">
                    <button type="button" wire:click="skipStep"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('onboarding.skip_button') }}
                    </button>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('onboarding.invite_submit_button') }}
                    </button>
                </div>
            </form>
        </div>

    {{-- Step 5: Done --}}
    @elseif ($step === 5)
        <div class="text-center">
            <div class="mb-6">
                <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ __('onboarding.done_heading') }}
                </h1>
                <p class="text-gray-600 mb-8">
                    {{ __('onboarding.done_description') }}
                </p>
            </div>
            <button type="button" wire:click="complete"
                class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('onboarding.complete_button') }}
            </button>
        </div>
    @endif
</div>
