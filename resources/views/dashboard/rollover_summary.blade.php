@extends('backend.layouts.app')

@section('title')
    {{ __('Month End Rollover Summary') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-7xl md:p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('Rollover Summary for') }} {{ $month }}
        </h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                        {{ __('Home') }}
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li class="text-sm text-gray-800 dark:text-white/90">{{ __('Rollover Summary') }}</li>
            </ol>
        </nav>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="text-lg font-medium text-gray-800 dark:text-white mb-4">
            {{ __('Total Amount Rolled Over to Savings') }}:
        </div>
        <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-6">
            {{ number_format($total_remaining, 2) }} {{ config('app.currency', 'Pkr') }}
        </div>

        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">{{ __('Budget Details') }}</h3>
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-700 text-left">
                    <th class="px-4 py-2">{{ __('Category') }}</th>
                    <th class="px-4 py-2">{{ __('Budgeted') }}</th>
                    <th class="px-4 py-2">{{ __('Used') }}</th>
                    <th class="px-4 py-2">{{ __('Remaining') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($budgets as $budget)
                    @php
                        $used = $budget->transactions()->sum('amount');
                        $remaining = $budget->amount - $used;
                    @endphp
                    <tr class="border-t dark:border-gray-700">
                        <td class="px-4 py-2">{{ $budget->category->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ number_format($budget->amount, 2) }}</td>
                        <td class="px-4 py-2 text-red-500">{{ number_format($used, 2) }}</td>
                        <td class="px-4 py-2 text-green-600">{{ number_format($remaining, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
