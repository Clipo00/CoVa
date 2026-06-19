@extends('layouts.auth')

@section('title', __('auth.mfa_challenge_title'))
@section('subtitle', __('auth.mfa_challenge_subtitle'))

@section('content')
    <livewire:auth.forms.mfa-challenge-form />
@endsection
