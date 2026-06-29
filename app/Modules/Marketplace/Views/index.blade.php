@extends('layouts.landing')

@section('title', __('marketplace.marketplace_title'))

@section('content')
    <div class="pt-24 pb-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-8">
                {{ __('marketplace.marketplace_title') }}
            </h1>

            <livewire:marketplace.list />
        </div>
    </div>
@endsection
