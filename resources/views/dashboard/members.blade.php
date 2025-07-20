@extends('backend.layouts.app')

@section('title')
    {{ __('Family Members') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('Family Members') }}
        </h2>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Accepted Members') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-light">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left">{{ __('#') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Name') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Email') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $index => $member)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4">{{ $index + 1 }}</td>
                                <td class="px-5 py-4">{{ $member->user->name ?? 'N/A' }}</td>
                                <td class="px-5 py-4">{{ $member->user->email ?? 'N/A' }}</td>
                                <td class="px-5 py-4 capitalize">{{ $member->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500 dark:text-gray-400">{{ __('No members found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
