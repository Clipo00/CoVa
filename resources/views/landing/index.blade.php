@extends('layouts.landing')

@section('title', __('landing.site_title'))

@section('content')
    {{-- Hero Section --}}
    @include('landing.partials.hero')

    {{-- Pain Point Section --}}
    @include('landing.partials.pain-point')

    {{-- How it Works Section --}}
    @include('landing.partials.how-it-works')

    {{-- Pricing Section --}}
    @include('landing.partials.pricing')

    {{-- Demo Section --}}
    @include('landing.partials.demo')

    {{-- Marketplace Preview Section --}}
    @if($marketplaceEnabled ?? false)
        @include('landing.partials.marketplace-preview')
    @endif

    {{-- Final CTA Section --}}
    @include('landing.partials.cta-final')
@endsection
