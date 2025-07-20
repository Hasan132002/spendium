@extends('backend.layouts.app')

@section('title', 'Comments')

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold">Comments on Post by {{ $post->user->name }}</h2>
        <a href="{{ url('admin/posts') }}" class="btn-default">Back to Posts</a>
    </div>

    <div class="rounded-2xl border bg-white dark:border-gray-800 dark:bg-gray-900 p-6">
        <h3 class="text-lg font-semibold mb-4">{{ $post->title }}</h3>
        <p class="text-gray-800 dark:text-white/90 mb-6">{{ $post->description }}</p>

        <h4 class="text-md font-semibold mb-3">Comments</h4>
        @forelse($comments as $comment)
            <div class="border rounded-lg p-4 mb-4 bg-gray-50 dark:bg-gray-800">
                <div class="font-medium text-sm">{{ $comment->user->name }}</div>
                <div class="text-gray-700 mt-1">{{ $comment->content }}</div>
                <div class="text-xs text-gray-500 mt-2">{{ $comment->reaction_count ?? 0 }} Reactions</div>
            </div>
        @empty
            <p>No comments yet.</p>
        @endforelse
    </div>
</div>
@endsection
