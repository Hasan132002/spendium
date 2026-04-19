@extends('backend.layouts.app')

@section('title')
    {{ __('Reports & Analytics') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('Reports & Analytics') }}
        </h2>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="mb-6 p-4 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ $from }}" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ $to }}" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">{{ __('Scope') }}</label>
                <select name="scope" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="mine" {{ $scope === 'mine' ? 'selected' : '' }}>{{ __('My data') }}</option>
                    @if ($family)
                        <option value="family" {{ $scope === 'family' ? 'selected' : '' }}>{{ __('Whole family') }}</option>
                    @endif
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">{{ __('Apply') }}</button>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.reports.export-expenses', ['from' => $from, 'to' => $to, 'scope' => $scope]) }}" class="btn-default text-sm"><i class="bi bi-download mr-1"></i> {{ __('Expenses CSV') }}</a>
                <a href="{{ route('admin.reports.export-incomes', ['from' => $from, 'to' => $to, 'scope' => $scope]) }}" class="btn-default text-sm"><i class="bi bi-download mr-1"></i> {{ __('Incomes CSV') }}</a>
            </div>
        </div>
    </form>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl p-5 border border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20">
            <p class="text-xs uppercase font-semibold text-green-700 dark:text-green-300">{{ __('Total Income') }}</p>
            <p class="mt-2 text-2xl font-bold text-green-700 dark:text-green-300">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $totalIncomes, 2) }}</p>
        </div>
        <div class="rounded-2xl p-5 border border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20">
            <p class="text-xs uppercase font-semibold text-red-700 dark:text-red-300">{{ __('Total Expenses') }}</p>
            <p class="mt-2 text-2xl font-bold text-red-700 dark:text-red-300">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $totalExpenses, 2) }}</p>
        </div>
        <div class="rounded-2xl p-5 border border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-900/20">
            <p class="text-xs uppercase font-semibold text-blue-700 dark:text-blue-300">{{ __('Net Savings') }}</p>
            <p class="mt-2 text-2xl font-bold text-blue-700 dark:text-blue-300 {{ $netSavings < 0 ? '!text-red-600' : '' }}">
                {{ config('app.currency_symbol', '$') }}{{ number_format((float) $netSavings, 2) }}
            </p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="rounded-2xl p-5 border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-base font-semibold mb-4">{{ __('Expenses by Category') }}</h3>
            @if ($expensesByCategory->count() > 0)
                <div id="category-pie"></div>
            @else
                <p class="text-center text-gray-500 py-12">{{ __('No expense data for this range.') }}</p>
            @endif
        </div>

        <div class="rounded-2xl p-5 border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-base font-semibold mb-4">{{ __('6-Month Income vs Expense Trend') }}</h3>
            <div id="trend-line"></div>
        </div>
    </div>

    {{-- Category table --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="text-base font-semibold">{{ __('Expense Breakdown') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">{{ __('Category') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Amount') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('% of total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expensesByCategory as $cat => $amount)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-3">{{ $cat }}</td>
                            <td class="px-5 py-3 text-right">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $amount, 2) }}</td>
                            <td class="px-5 py-3 text-right">{{ $totalExpenses > 0 ? number_format(($amount / $totalExpenses) * 100, 1) : '0.0' }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500">{{ __('No expenses recorded for this range.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const catLabels = @json($expensesByCategory->keys());
        const catValues = @json(array_values($expensesByCategory->toArray()));

        if (catLabels.length > 0 && document.querySelector('#category-pie')) {
            new ApexCharts(document.querySelector('#category-pie'), {
                chart: { type: 'donut', height: 300 },
                series: catValues,
                labels: catLabels,
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + '%' },
            }).render();
        }

        const trendMonths = @json(array_keys($period));
        const incomeSeries = @json(array_column($period, 'income'));
        const expenseSeries = @json(array_column($period, 'expense'));

        if (document.querySelector('#trend-line')) {
            new ApexCharts(document.querySelector('#trend-line'), {
                chart: { type: 'area', height: 300, toolbar: { show: false } },
                series: [
                    { name: 'Income', data: incomeSeries },
                    { name: 'Expense', data: expenseSeries },
                ],
                xaxis: { categories: trendMonths },
                colors: ['#10b981', '#ef4444'],
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
                legend: { position: 'top' },
                dataLabels: { enabled: false },
            }).render();
        }
    });
</script>
@endpush
@endsection
