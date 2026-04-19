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
        @can('personal.fund_request.create')
            <a href="{{ route('admin.fund-requests.create') }}" class="btn-primary text-sm">
                <i class="bi bi-plus-lg mr-1"></i> {{ __('Request Funds') }}
            </a>
        @endcan
    </div>

    @include('backend.layouts.partials.messages')

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
                                <td class="px-5 py-4">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $request->amount, 2) }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-block text-xs px-2 py-0.5 rounded-full capitalize
                                        @if ($request->status === 'approved') bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300
                                        @elseif ($request->status === 'rejected') bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                                        @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300 @endif">
                                        {{ $request->status }}
                                    </span>
                                </td>
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
