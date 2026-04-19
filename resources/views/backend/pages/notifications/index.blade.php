@extends('backend.layouts.app')

@section('title')
    {{ __('Notifications') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('Notifications') }}
        </h2>
        @if ($notifications->count() > 0)
            <form action="{{ route('admin.notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn-default">
                    <i class="bi bi-check2-all mr-1"></i> {{ __('Mark all as read') }}
                </button>
            </form>
        @endif
    </div>

    @include('backend.layouts.partials.messages')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        @forelse ($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $url = $data['url'] ?? route('admin.notifications.mark-read', $notification->id);
                $icon = $data['icon'] ?? 'bi-bell';
                $title = $data['title'] ?? 'Notification';
                $message = $data['message'] ?? '';
                $isUnread = $notification->read_at === null;
            @endphp
            <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 {{ $isUnread ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }} hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                    <i class="bi {{ $icon }} text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('admin.notifications.mark-read', $notification->id) }}" class="block">
                        <p class="text-sm font-medium text-gray-900 dark:text-white/90">
                            {{ $title }}
                            @if ($isUnread)
                                <span class="ml-2 inline-block w-2 h-2 bg-blue-500 rounded-full"></span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $message }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                            {{ $notification->created_at?->diffForHumans() }}
                        </p>
                    </a>
                </div>
                <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="{{ __('Delete') }}">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <i class="bi bi-bell-slash text-4xl text-gray-300 dark:text-gray-700"></i>
                <p class="mt-3 text-gray-500 dark:text-gray-400">{{ __('No notifications yet.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
