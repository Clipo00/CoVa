@extends('layouts.app')

@section('title', $blueprint->title)

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold">{{ $blueprint->title }}</h1>
            <p class="mt-2 text-gray-600">{{ $blueprint->description }}</p>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-4">
                <span class="text-sm text-gray-500">UUID: {{ $blueprint->uuid }}</span>
            </div>

            <div class="border-t pt-4">
                <h2 class="text-lg font-semibold mb-4">Variables</h2>
                @if($blueprint->variables->isEmpty())
                    <p class="text-gray-500">No hay variables configuradas.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($blueprint->variables as $variable)
                            <li class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <span class="font-mono">{{ $variable->key }}</span>
                                <span class="text-sm text-gray-600">{{ $variable->type }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection
