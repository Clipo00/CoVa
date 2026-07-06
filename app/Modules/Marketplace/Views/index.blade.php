@extends('layouts.app')

@section('title', __('marketplace.marketplace_title'))

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('marketplace.marketplace_title') }}
            </h1>
        </div>

        <livewire:marketplace.list />
    </div>
@endsection
