@extends('layouts.public')

@section('title', __('Community Forum') . ' | ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Community Forum') }}</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-2">
        {{ __('See what Spendium families are sharing — finance tips, savings journeys, and money milestones.') }}
    </p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse ($posts as $post)
        <article class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden hover:shadow-theme-md transition-shadow">
            @if ($post->photo)
                <img src="{{ $post->photo }}" alt="" class="w-full h-48 object-cover">
            @endif
            <div class="p-5">
                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-medium">
                        {{ strtoupper(substr($post->user?->name ?? '?', 0, 1)) }}
                    </span>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $post->user?->name ?? '—' }}</span>
                    <span>·</span>
                    <span>{{ $post->created_at?->diffForHumans() }}</span>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    <a href="{{ route('community.show', $post->id) }}" class="hover:text-brand-500">{{ $post->title }}</a>
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">{{ $post->description }}</p>
                <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                    <span><i class="bi bi-chat-dots"></i> {{ $post->comments_count }}</span>
                    <span><i class="bi bi-heart"></i> {{ $post->reactions_count }}</span>
                    <a href="{{ route('community.show', $post->id) }}" class="ml-auto text-brand-500 hover:underline">{{ __('Read more →') }}</a>
                </div>
            </div>
        </article>
    @empty
        <div class="col-span-full rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-12 text-center">
            <i class="bi bi-chat-square-dots text-5xl text-gray-300 dark:text-gray-700"></i>
            <p class="mt-3 text-gray-500 dark:text-gray-400">{{ __('No posts yet. Be the first to share!') }}</p>
            @guest
                <a href="{{ route('register') }}" class="btn-primary mt-4 inline-block">{{ __('Sign Up to Post') }}</a>
            @endguest
        </div>
    @endforelse
</div>

@if ($posts->hasPages())
    <div class="mt-8">{{ $posts->links() }}</div>
@endif

@guest
    <div class="mt-10 p-6 rounded-2xl border border-brand-200 bg-brand-50 dark:border-brand-900 dark:bg-brand-900/20 text-center">
        <h3 class="text-lg font-semibold text-brand-700 dark:text-brand-300">{{ __('Want to join the conversation?') }}</h3>
        <p class="text-sm text-brand-600 dark:text-brand-400 mt-1">{{ __('Create your Spendium family account to post, comment, and react.') }}</p>
        <a href="{{ route('register') }}" class="btn-primary mt-4 inline-block">{{ __('Create Family Account') }}</a>
    </div>
@endguest
@endsection
