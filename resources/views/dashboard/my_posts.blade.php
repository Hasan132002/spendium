@extends('backend.layouts.app')

@section('title', 'My Posts')

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <h2 class="text-xl font-semibold">My Posts</h2>
    <a href="{{ url('admin/posts') }}" class="btn-default mt-2 inline-block">Back to Posts</a>

    <div class="space-y-6 mt-6">
        <div class="rounded-2xl border bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="px-6 py-4">
                <h3 class="text-lg font-semibold">Your Posts</h3>
            </div>
            <div class="border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-light">
                        <tr>
                            <th class="p-3">#</th>
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
                                <td class="p-3">
                                    @if($post->photo)
                                        <img src="{{ asset('storage/' . $post->photo) }}" class="h-14 w-20 rounded-md object-cover">
                                    @else
                                        <span>No Image</span>
                                    @endif
                                </td>
                                <td class="p-3">{{ Str::limit($post->description, 50) }}</td>
                                <td class="p-3">{{ $post->comments->count() }}</td>
                                <td class="p-3">{{ $post->reactions->count() }}</td>
                                <td class="p-3">
                                    <a href="{{ url('admin/posts/' . $post->id) }}" class="btn-default">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4">You have no posts yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
