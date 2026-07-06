@extends('layouts.app')

@section('title', __('landing.pricing_title'))

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('landing.pricing_title') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('landing.pricing_subtitle') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
        @foreach ($plans as $plan)
            @php
                $isCurrentPlan = auth()->check() && auth()->user()->plan_id === $plan->id;
                $isPro = $plan->slug === 'pro';
                $isEnterprise = $plan->slug === 'enterprise';
                $isFree = $plan->slug === 'free';
            @endphp

            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border {{ $isCurrentPlan ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-gray-200 dark:border-gray-700' }} flex flex-col">
                @if ($isPro)
                    <div class="absolute -top-3 left-0 right-0 flex justify-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-600 text-white shadow-sm">
                            {{ __('landing.plan_popular') }}
                        </span>
                    </div>
                @endif

                <div class="p-6 flex-1">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('landing.plan_name_' . $plan->slug) }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('landing.plan_' . $plan->slug . '_desc') }}</p>

                    <div class="mt-4">
                        @if ($plan->price_monthly === null)
                            <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ __('landing.plan_price_custom') }}</span>
                        @elseif ($plan->price_monthly == 0)
                            <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">0 €</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('landing.per_month') }}</span>
                        @else
                            <span class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format((float) $plan->price_monthly, 2, ',', '.') }} €</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('landing.per_month') }}</span>
                        @endif
                    </div>

                    <ul class="mt-6 space-y-3">
                        <li class="flex items-start text-sm">
                            @if ($plan->max_organizations_per_user === null)
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_orgs_unlimited') }}</span>
                            @else
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_orgs', ['count' => $plan->max_organizations_per_user]) }}</span>
                            @endif
                        </li>
                        <li class="flex items-start text-sm">
                            @if ($plan->max_blueprints_per_org === null)
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_blueprints_unlimited') }}</span>
                            @else
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_blueprints', ['count' => $plan->max_blueprints_per_org]) }}</span>
                            @endif
                        </li>
                        <li class="flex items-start text-sm">
                            @if ($plan->max_members_per_org === null)
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_members_unlimited') }}</span>
                            @else
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_members', ['count' => $plan->max_members_per_org]) }}</span>
                            @endif
                        </li>
                        <li class="flex items-start text-sm">
                            @if ($plan->max_variables_per_blueprint === null)
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_variables_unlimited') }}</span>
                            @else
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_variables', ['count' => $plan->max_variables_per_blueprint]) }}</span>
                            @endif
                        </li>
                        <li class="flex items-start text-sm">
                            @if ($plan->has_api_access)
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_api_access') }}</span>
                            @else
                                <span class="text-red-400 dark:text-red-300 mr-2">✕</span>
                                <span class="text-gray-400 dark:text-gray-500">{{ __('landing.plan_api_access') }}</span>
                            @endif
                        </li>
                        <li class="flex items-start text-sm">
                            @if ($plan->has_marketplace_publish)
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_marketplace_publish') }}</span>
                            @else
                                <span class="text-red-400 dark:text-red-300 mr-2">✕</span>
                                <span class="text-gray-400 dark:text-gray-500">{{ __('landing.plan_marketplace_browse') }}</span>
                            @endif
                        </li>
                        @if ($isEnterprise)
                            <li class="flex items-start text-sm">
                                <span class="text-green-500 dark:text-green-400 mr-2">✓</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ __('landing.plan_priority_support') }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="p-6 pt-0">
                    @if ($isCurrentPlan)
                        <span class="block w-full text-center py-2.5 px-4 rounded-lg text-sm font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            {{ __('landing.plan_current') }}
                        </span>
                    @elseif ($isFree)
                        <span class="block w-full text-center py-2.5 px-4 rounded-lg text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                            {{ __('landing.plan_cta_free') }}
                        </span>
                    @elseif ($isPro)
                        @php
                            $canStartTrial = auth()->check() && auth()->user()->plan_id === optional(\App\Modules\Shared\Models\Plan::where('slug', 'free')->first())->id && auth()->user()->trial_used_at === null;
                        @endphp
                        @if ($canStartTrial)
                            <form method="POST" action="{{ route('pricing.start-trial') }}">
                                @csrf
                                <button type="submit" class="block w-full text-center py-2.5 px-4 rounded-lg text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors cursor-pointer">
                                    {{ __('landing.plan_cta_trial') }}
                                </button>
                            </form>
                        @else
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('landing.coming_soon') }}' } }))" class="block w-full text-center py-2.5 px-4 rounded-lg text-sm font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition-colors cursor-pointer">
                                {{ auth()->user()->trial_used_at ? __('landing.plan_cta_pro_no_trial') : __('landing.plan_cta_pro') }}
                            </button>
                        @endif
                    @elseif ($isEnterprise)
                        @if(config('marketplace.billing_enabled'))
                            <a href="mailto:covarapp@gmail.com" class="block w-full text-center py-2.5 px-4 rounded-lg text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:opacity-90 transition-colors cursor-pointer">
                                {{ __('landing.plan_cta_enterprise') }}
                            </a>
                        @else
                            <button type="button" onclick="window.dispatchEvent(new CustomEvent('notify', { detail: { message: '{{ __('landing.coming_soon') }}' } }))" class="block w-full text-center py-2.5 px-4 rounded-lg text-sm font-medium bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:opacity-90 transition-colors cursor-pointer">
                                {{ __('landing.coming_soon') }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <p class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
        {{ __('landing.pricing_note') }}
    </p>
@endsection
