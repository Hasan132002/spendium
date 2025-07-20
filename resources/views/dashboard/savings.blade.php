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
