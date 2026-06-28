@extends('layouts.auth')

@section('title', __('auth.mfa_setup_title'))
@section('subtitle', __('auth.mfa_setup_desc'))

@section('content')
    <livewire:auth.forms.mfa-setup-form />
@endsection
