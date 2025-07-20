@extends('backend.layouts.app')

@section('title', 'Followers')

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <h2 class="text-xl font-semibold mb-4">Followers of {{ $user->name }}</h2>
    <a href="{{ url()->previous() }}" class="btn-default mb-4">Go Back</a>

    @forelse($followers as $follower)
        <div class="border p-4 rounded-lg mb-4 bg-white dark:bg-gray-900">
            <h3 class="font-medium">{{ $follower->name }}</h3>
            <p class="text-sm text-gray-600">{{ $follower->email }}</p>
            <div class="mt-2 text-sm text-gray-700">
                Posts: {{ $follower->posts->count() }}
            </div>
        </div>
    @empty
        <p>No followers found.</p>
    @endforelse
</div>
@endsection
