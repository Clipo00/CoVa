@extends('layouts.auth')

@section('title', __('onboarding.page_title'))
@section('subtitle', __('onboarding.page_subtitle'))

@section('content')
    <livewire:auth.forms.onboarding-wizard />
@endsection
