@extends('backend.layouts.app')

@section('title', 'All Posts')

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90 mb-6">All Posts</h2>

    <div class="space-y-6">
        <div class="rounded-2xl border bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="px-6 py-4">
                <h3 class="text-lg font-semibold">Post List</h3>
            </div>
            <div class="border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                @include('backend.layouts.partials.messages')
                <table class="w-full table-auto">
                    <thead class="bg-light">
                        <tr>
                            <th class="p-3">#</th>
                            <th class="p-3">Author</th>
                            <th class="p-3">Image</th>
                            <th class="p-3">Content</th>
                            <th class="p-3">Comments</th>
                            <th class="p-3">Likes</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr class="border-b">
                                <td class="p-3">{{ $loop->iteration }}</td>
                                <td class="p-3">{{ $post->user->name }}</td>
                                <td class="p-3">
                                    @if($post->photo)
                                        <img src="{{ asset($post->photo) }}" class="h-14 w-20 rounded-md object-cover">
                                    @else
                                        <span>No Image</span>
                                    @endif
                                </td>
                                <td class="p-3">{{ Str::limit($post->description, 50) }}</td>
                                <td class="p-3">{{ $post->comment_count }}</td>
                                <td class="p-3">{{ $post->like_count }}</td>
                                <td class="p-3">
                                    <a href="{{ url('admin/posts/' . $post->id) }}" class="btn-default">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4">No posts found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
