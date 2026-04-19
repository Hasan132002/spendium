@extends('backend.layouts.app')

@section('title')
    {{ __('Savings') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <div x-data="{ pageName: '{{ __('Savings') }}' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ __('My Savings') }}
                <span class="ml-3 text-2xl font-bold text-blue-600">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $saving->total, 2) }}</span>
            </h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                           href="{{ route('admin.dashboard') }}">
                            {{ __('Home') }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90">{{ __('Savings') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    @include('backend.layouts.partials.messages')

    @can('personal.savings.manage')
    {{-- Add to Savings --}}
    <div class="mb-6 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Add to Savings') }}</h3>
        </div>
        <form action="{{ route('admin.savings.add') }}" method="POST" class="p-5 flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Amount') }}</label>
                <input type="number" step="0.01" name="amount" required placeholder="e.g. 1000"
                       class="h-10 w-40 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Note (optional)') }}</label>
                <input type="text" name="note" placeholder="e.g. Monthly deposit"
                       class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>
            <button type="submit" class="btn-primary text-sm"><i class="bi bi-plus-lg mr-1"></i>{{ __('Add') }}</button>
        </form>
    </div>
    @endcan

<div class="mt-8 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="px-5 py-4 sm:px-6 sm:py-5">
        <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">{{ __('Saving Transaction History') }}</h3>

        @if($saving->transactions->count() > 0)
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full table-auto text-left border-collapse">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Date') }}</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Amount') }}</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Type') }}</th>
                            <th class="px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">{{ __('Note') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($saving->transactions as $transaction)
                            <tr>
                                <td class="px-4 py-2 text-gray-800 dark:text-white/80">
                                    {{ $transaction->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-2 text-gray-800 dark:text-white/80">
                                    {{ number_format($transaction->amount, 2) }} {{ config('app.currency_symbol', '$') }}
                                </td>
                                <td class="px-4 py-2 text-gray-800 dark:text-white/80">
                                    {{ ucfirst($transaction->type) }}
                                </td>
                                <td class="px-4 py-2 text-gray-800 dark:text-white/80">
                                    {{ $transaction->note ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mt-4 text-gray-600 dark:text-gray-300">
                {{ __('No transactions found.') }}
            </div>
        @endif
    </div>
</div>

</div>
@endsection
