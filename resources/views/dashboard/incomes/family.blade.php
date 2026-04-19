@extends('backend.layouts.app')

@section('title')
    {{ __('Family Incomes') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <h2 class="mb-6 text-xl font-semibold text-gray-800 dark:text-white/90">
        {{ __('Family Incomes') }}
    </h2>

    @include('backend.layouts.partials.messages')

    <div class="mb-4 p-4 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Member') }}</label>
                <select name="user_id" class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($members as $m)
                        <option value="{{ $m->user_id }}" {{ request('user_id') == $m->user_id ? 'selected' : '' }}>{{ $m->user?->name }}</option>
                    @endforeach
                </select>
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
                <a href="{{ route('admin.incomes.family') }}" class="btn-default">{{ __('Clear') }}</a>
            </div>
        </form>
    </div>

    <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20 p-4">
        <p class="text-sm text-green-700 dark:text-green-300">
            {{ __('Total for filtered range') }}:
            <span class="font-semibold">{{ config('app.currency_symbol', '$') }}{{ number_format((float) $totalForRange, 2) }}</span>
        </p>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 text-left text-xs uppercase text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">{{ __('Date') }}</th>
                        <th class="px-5 py-3">{{ __('Member') }}</th>
                        <th class="px-5 py-3">{{ __('Title') }}</th>
                        <th class="px-5 py-3">{{ __('Source') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($incomes as $income)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">{{ $income->received_on?->format('d M Y') }}</td>
                            <td class="px-5 py-4">{{ $income->user?->name }}</td>
                            <td class="px-5 py-4">{{ $income->title }}</td>
                            <td class="px-5 py-4 capitalize">{{ $income->source }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-green-600">+{{ config('app.currency_symbol', '$') }}{{ number_format((float) $income->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500">{{ __('No family incomes yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($incomes->hasPages())
        <div class="mt-6">{{ $incomes->links() }}</div>
    @endif
</div>
@endsection
