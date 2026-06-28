@extends('layouts.auth')

@section('title', __('auth.password_reset_title'))
@section('subtitle', __('auth.password_reset_subtitle'))

@section('content')
    <livewire:auth.forms.forgot-password-form />
@endsection
