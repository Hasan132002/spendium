@extends('backend.layouts.app')

@section('title', 'Followings')

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <h2 class="text-xl font-semibold mb-4">Followings of {{ $user->name }}</h2>
    <a href="{{ url()->previous() }}" class="btn-default mb-4">Go Back</a>

    @forelse($followings as $following)
        <div class="border p-4 rounded-lg mb-4 bg-white dark:bg-gray-900">
            <h3 class="font-medium">{{ $following->name }}</h3>
            <p class="text-sm text-gray-600">{{ $following->email }}</p>
            <div class="mt-2 text-sm text-gray-700">
                Posts: {{ $following->posts->count() }}
            </div>
        </div>
    @empty
        <p>No followings found.</p>
    @endforelse
</div>
@endsection
