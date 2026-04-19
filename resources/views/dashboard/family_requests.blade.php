@extends('backend.layouts.app')

@section('title')
    {{ __('Family Requests') }} | {{ config('app.name') }}
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('Family Fund Requests') }}
        </h2>
    </div>

    @include('backend.layouts.partials.messages')

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Family Fund Requests') }}</h3>
            </div>
            <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-800">
                <table class="w-full">
                    <thead class="bg-light">
                        <tr class="border-b border-gray-100 dark:border-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3 text-left">{{ __('User') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Category') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Amount') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Note') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Status') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Created') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">{{ $request->user->name ?? '-' }}</td>
                                <td class="px-5 py-4">{{ $request->category->name ?? '-' }}</td>
                                <td class="px-5 py-4 font-semibold">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $request->amount, 2) }}</td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">{{ $request->note ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-block text-xs px-2 py-0.5 rounded-full capitalize
                                        @if ($request->status === 'approved') bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300
                                        @elseif ($request->status === 'rejected') bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                                        @else bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300 @endif">
                                        {{ $request->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm">{{ $request->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-right">
                                    @if ($request->status === 'pending')
                                        @can('family.fund_request.approve')
                                            <form action="{{ route('admin.fund-requests.approve', $request->id) }}" method="POST" class="inline" onsubmit="return confirm('Approve this request for {{ number_format((float) $request->amount, 2) }}?');">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:underline text-sm mr-3"><i class="bi bi-check-circle"></i> {{ __('Approve') }}</button>
                                            </form>
                                            <form action="{{ route('admin.fund-requests.decline', $request->id) }}" method="POST" class="inline" onsubmit="return confirm('Decline this request?');">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:underline text-sm"><i class="bi bi-x-circle"></i> {{ __('Decline') }}</button>
                                            </form>
                                        @endcan
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-500 dark:text-gray-400">{{ __('No family requests found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
