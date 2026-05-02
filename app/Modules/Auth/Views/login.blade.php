@extends('layouts.auth')

@section('title', 'Login')
@section('subtitle', 'Inicia sesión en tu cuenta')

@section('content')
    <livewire:auth.forms.login-form />
@endsection
