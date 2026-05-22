@extends('layouts.auth')

@section('title', __('auth.login_title'))
@section('subtitle', __('auth.login_subtitle'))

@section('content')
    <livewire:auth.forms.login-form />
@endsection
