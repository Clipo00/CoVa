@extends('layouts.auth')

@section('title', __('auth.login_title'))
@section('subtitle', __('auth.login_subtitle'))

@section('content')
    @if(request()->has('expired'))
        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md text-sm text-yellow-700">
            {{ __('auth.session_expired') }}
        </div>
    @endif
    <livewire:auth.forms.login-form />
@endsection
