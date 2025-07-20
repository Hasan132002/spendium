@extends('backend.layouts.app')

@section('title')
    {{ __('My Requests') }} | {{ config('app.name') }}
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('My Fund Requests') }}
        </h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                        {{ __('Home') }}
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li class="text-sm text-gray-800 dark:text-white/90">{{ __('My Requests') }}</li>
            </ol>
        </nav>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('My Fund Requests') }}</h3>
            </div>
            <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-800">
                <table class="w-full">
                    <thead class="bg-light">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left">{{ __('#') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Category') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Amount') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4">{{ $loop->iteration }}</td>
                                <td class="px-5 py-4">{{ $request->category->name ?? '-' }}</td>
                                <td class="px-5 py-4">{{ number_format($request->amount) }}</td>
                                <td class="px-5 py-4 capitalize">{{ $request->status }}</td>
                                <td class="px-5 py-4">{{ $request->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">{{ __('No requests found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
