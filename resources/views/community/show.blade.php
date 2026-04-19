@extends('layouts.public')

@section('title', $post->title . ' | ' . __('Community'))

@section('content')
<a href="{{ route('community.index') }}" class="text-sm text-brand-500 hover:underline mb-4 inline-block">← {{ __('Back to community') }}</a>

<article class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
    @if ($post->photo)
        <img src="{{ $post->photo }}" alt="" class="w-full max-h-96 object-cover">
    @endif
    <div class="p-8">
        <div class="flex items-center gap-3 text-sm text-gray-500 mb-4">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 font-medium">
                {{ strtoupper(substr($post->user?->name ?? '?', 0, 1)) }}
            </span>
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $post->user?->name ?? '—' }}</p>
                <p class="text-xs">{{ $post->created_at?->diffForHumans() }}</p>
            </div>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ $post->title }}</h1>
        <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $post->description }}</div>

        <div class="mt-6 flex items-center gap-6 text-sm text-gray-500">
            <span><i class="bi bi-chat-dots"></i> {{ $post->comments_count }} {{ __('comments') }}</span>
            <span><i class="bi bi-heart"></i> {{ $post->reactions_count }} {{ __('reactions') }}</span>
        </div>
    </div>
</article>

<section class="mt-8">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Comments') }} ({{ $post->comments->count() }})</h2>

    <div class="space-y-3">
        @forelse ($post->comments as $comment)
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-4">
                <div class="flex items-start gap-3">
                    <span class="inline-flex flex-shrink-0 items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 text-xs font-medium">
                        {{ strtoupper(substr($comment->user?->name ?? '?', 0, 1)) }}
                    </span>
                    <div class="flex-1">
                        <p class="text-sm"><span class="font-semibold text-gray-900 dark:text-white">{{ $comment->user?->name ?? '—' }}</span> <span class="text-xs text-gray-400 ml-2">{{ $comment->created_at?->diffForHumans() }}</span></p>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $comment->content }}</p>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-6">{{ __('No comments yet.') }}</p>
        @endforelse
    </div>

    @guest
        <div class="mt-6 p-4 rounded-xl border border-brand-200 bg-brand-50 dark:border-brand-900 dark:bg-brand-900/20 text-center">
            <p class="text-sm text-brand-700 dark:text-brand-300">
                <a href="{{ route('admin.login') }}" class="underline font-medium">{{ __('Log in') }}</a>
                {{ __('or') }}
                <a href="{{ route('register') }}" class="underline font-medium">{{ __('create an account') }}</a>
                {{ __('to comment and react.') }}
            </p>
        </div>
    @else
        <form action="{{ route('admin.posts.comment', $post->id) }}" method="POST" class="mt-6 p-4 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            @csrf
            <textarea name="content" rows="3" required placeholder="{{ __('Share your thoughts...') }}"
                      class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
            <div class="mt-3 flex justify-end">
                <button type="submit" class="btn-primary text-sm">{{ __('Post Comment') }}</button>
            </div>
        </form>
    @endguest
</section>
@endsection
