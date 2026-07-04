@extends('layouts.auth')

@section('title', __('auth.change_password_title'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/60 dark:border-gray-700/60 p-8 w-full max-w-md">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 text-center mb-6">
            {{ __('auth.change_password_title') }}
        </h2>
        <livewire:auth.forms.change-password-form />
    </div>
</div>
@endsection
