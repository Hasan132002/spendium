@extends('backend.layouts.app')

@section('title', 'User Profile Stats')

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Profile Stats: {{ $user->name }}</h2>
        <div class="text-sm text-gray-600">Email: {{ $user->email }}</div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
        <div class="p-4 border rounded-lg bg-white dark:bg-gray-900 text-center">
            <div class="text-2xl font-bold">{{ $totalPosts }}</div>
            <div class="text-gray-600">Total Posts</div>
        </div>
        <div class="p-4 border rounded-lg bg-white dark:bg-gray-900 text-center">
            <div class="text-2xl font-bold">{{ $uploadCount }}</div>
            <div class="text-gray-600">Uploads with Images</div>
        </div>
        <div class="p-4 border rounded-lg bg-white dark:bg-gray-900 text-center">
            <div class="text-2xl font-bold">{{ $followerCount }}</div>
            <div class="text-gray-600">Followers</div>
        </div>
        <div class="p-4 border rounded-lg bg-white dark:bg-gray-900 text-center">
            <div class="text-2xl font-bold">{{ $followingCount }}</div>
            <div class="text-gray-600">Following</div>
        </div>
    </div>

    <div class="mb-6">
        <h3 class="text-lg font-semibold mb-3">Posts</h3>

        @forelse($posts as $post)
            <div class="border rounded-xl p-4 mb-4 bg-white dark:bg-gray-900">
                <h4 class="font-medium mb-1">{{ $post->title }}</h4>
                
                @if($post->photo)
                    <img src="{{ asset('storage/' . $post->photo) }}" class="w-full max-w-xs h-40 object-cover rounded mb-2">
                @endif

                <p>{{ Str::limit($post->description, 150) }}</p>
                <div class="mt-3 flex gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <span>{{ $post->comments_count }} Comments</span>
                    <span>{{ $post->likes_count }} Likes</span>
                </div>

                @if($post->comments->count())
                    <div class="mt-4 border-t pt-2">
                        <h5 class="text-sm font-semibold mb-2">Recent Comments</h5>
                        @foreach($post->comments as $comment)
                            <div class="mb-2">
                                <strong>{{ $comment->user->name }}</strong>: {{ $comment->content }}
                                <div class="text-xs text-gray-500">Reactions: {{ $comment->reactions_count }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p>No posts available.</p>
        @endforelse
    </div>
</div>
@endsection
