@extends('layouts.auth')

@section('title', __('auth.register_title'))
@section('subtitle', __('auth.register_subtitle'))

@section('content')
    <livewire:auth.forms.register-form />
@endsection
