@extends('backend.layouts.app')

@section('title')
    {{ __('My Incomes') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('My Incomes') }}
        </h2>
        <a href="{{ route('admin.incomes.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg mr-2"></i> {{ __('Record Income') }}
        </a>
    </div>

    @include('backend.layouts.partials.messages')

    {{-- Filter form --}}
    <div class="mb-4 p-4 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Source') }}</label>
                <select name="source" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['salary','business','freelance','rental','investment','gift','other'] as $src)
                        <option value="{{ $src }}" {{ request('source') === $src ? 'selected' : '' }}>{{ ucfirst($src) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">{{ __('Apply') }}</button>
                <a href="{{ route('admin.incomes.my') }}" class="btn-default">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>

    {{-- Total --}}
    <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20 p-4">
        <p class="text-sm text-green-700 dark:text-green-300">
            {{ __('Total for filtered range') }}:
            <span class="font-semibold">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $totalForRange, 2) }}</span>
        </p>
    </div>

    {{-- Income list --}}
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">{{ __('Date') }}</th>
                        <th class="px-5 py-3">{{ __('Title') }}</th>
                        <th class="px-5 py-3">{{ __('Source') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Amount') }}</th>
                        <th class="px-5 py-3">{{ __('Recurring') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incomes as $income)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-4">{{ $income->received_on?->format('d M Y') }}</td>
                            <td class="px-5 py-4 font-medium text-gray-900 dark:text-white/90">{{ $income->title }}</td>
                            <td class="px-5 py-4 capitalize">{{ $income->source }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-green-600 dark:text-green-400">
                                +{{ config('app.currency_symbol', '$') }}{{ number_format((float) $income->amount, 2) }}
                            </td>
                            <td class="px-5 py-4">
                                @if ($income->recurring)
                                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        <i class="bi bi-arrow-repeat"></i> {{ ucfirst($income->recurrence_interval ?? 'monthly') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form action="{{ route('admin.incomes.destroy', $income->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this income?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-sm"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-500">{{ __('No incomes yet. Record your first one!') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($incomes->hasPages())
        <div class="mt-6">
            {{ $incomes->links() }}
        </div>
    @endif
</div>
@endsection
