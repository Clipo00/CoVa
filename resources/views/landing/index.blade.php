@extends('layouts.landing')

@section('title', __('landing.site_title'))

@section('content')
    {{-- Tab: Inicio — Hero + Pain Point + How it Works --}}
    <div role="tabpanel" id="panel-inicio" aria-labelledby="tab-inicio"
        x-show="activeTab === 'inicio'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        @include('landing.partials.hero')
        @include('landing.partials.pain-point')
        @include('landing.partials.how-it-works')
    </div>

    {{-- Tab: Demo — Carousel --}}
    <div role="tabpanel" id="panel-demo" aria-labelledby="tab-demo"
        x-show="activeTab === 'demo'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        @include('landing.partials.demo')
    </div>

    {{-- Tab: Precios — Pricing --}}
    <div role="tabpanel" id="panel-precios" aria-labelledby="tab-precios"
        x-show="activeTab === 'precios'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        @include('landing.partials.pricing')
    </div>

    {{-- Tab: Marketplace — Public blueprints showcase --}}
    <div role="tabpanel" id="panel-marketplace" aria-labelledby="tab-marketplace"
        x-show="activeTab === 'marketplace'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        @include('landing.partials.marketplace-preview')
    </div>

    {{-- Tab: Docs — CLI quickstart guide --}}
    <div role="tabpanel" id="panel-docs" aria-labelledby="tab-docs"
        x-show="activeTab === 'docs'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        @include('landing.partials.docs')
    </div>

    {{-- Final CTA (outside tabs, always visible) --}}
    @include('landing.partials.cta-final')
@endsection
