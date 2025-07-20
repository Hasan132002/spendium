@extends('backend.layouts.app')

@section('title')
    {{ __('Personal Goals') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('My Personal Goals') }}
        </h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                        {{ __('Home') }}
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li class="text-sm text-gray-800 dark:text-white/90">{{ __('Personal Goals') }}</li>
            </ol>
        </nav>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Personal Goals List') }}</h3>
            </div>

            <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-800">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 dark:bg-gray-800 dark:text-white">
                        <tr>
                            <th class="px-6 py-3">{{ __('Sl') }}</th>
                            <th class="px-6 py-3">{{ __('User') }}</th>
                            <th class="px-6 py-3">{{ __('Title') }}</th>
                            <th class="px-6 py-3">{{ __('Target Amount') }}</th>
                            <th class="px-6 py-3">{{ __('Collected') }}</th>
                            <th class="px-6 py-3">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($goals as $goal)
                            <tr>
                                <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4">
                                    {{ $goal->user->name ?? auth()->user()->name }}
                                </td>
                                <td class="px-6 py-4">{{ $goal->title ?? 'N/A' }}</td>
                                <td class="px-6 py-4">{{ number_format($goal->target_amount) }}</td>
                                <td class="px-6 py-4 text-green-600">{{ number_format($goal->collected_amount ?? 0) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-full 
                                        {{ $goal->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-white' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-white' }}">
                                        {{ ucfirst($goal->status ?? 'pending') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('No goals found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
