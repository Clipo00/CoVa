@extends('layouts.auth')

@section('title', 'Registro')
@section('subtitle', 'Crea tu cuenta gratuita')

@section('content')
    <livewire:auth.forms.register-form />
@endsection
