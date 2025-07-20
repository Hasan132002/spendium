@extends('backend.layouts.app')

@section('title', 'Post Detail')

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <h2 class="text-xl font-semibold mb-4">Post by {{ $post->user->name }}</h2>
    <a href="{{ url('admin/posts') }}" class="btn-default mb-6 inline-block">Back to Posts</a>

    <div class="rounded-2xl border bg-white dark:border-gray-800 dark:bg-gray-900 p-6">
        @if($post->photo)
            <img src="{{ asset($post->photo) }}" class="w-full h-72 object-cover rounded-lg mb-4">
        @endif
        <h3 class="text-lg font-semibold">{{ $post->title }}</h3>
        <p class="mt-2 text-gray-800 dark:text-white/90">{{ $post->description }}</p>
        <div class="mt-4 flex gap-6 text-sm text-gray-600 dark:text-gray-400">
            <span>{{ $post->comments->count() }} Comments</span>
            <span>{{ $post->reactions->count() }} Likes</span>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-lg font-semibold">Comments</h3>
        <div class="space-y-4 mt-4">
            @forelse($post->comments as $comment)
                <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="font-medium">{{ $comment->user->name }}</div>
                    <div class="mt-1">{{ $comment->content }}</div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ $comment->reaction_count ?? 0 }} Reactions
                    </div>
                </div>
            @empty
                <p class="text-gray-500">No comments yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
