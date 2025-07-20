@extends('backend.layouts.app')

@section('title')
    {{ __('Loan Detail') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-7xl md:p-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90 mb-6">{{ __('Loan Detail') }}</h2>
    <div class="space-y-4">
        <div class="p-4 rounded-lg bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800">
            <h3 class="font-semibold text-lg">{{ __('Category') }}: {{ $loan->category->name }}</h3>
            <p>{{ __('Amount') }}: {{ number_format($loan->amount, 2) }}</p>
        </div>

        <div class="p-4 rounded-lg bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800">
            <h3 class="font-semibold text-lg mb-3">{{ __('Repayments') }}</h3>
            <ul class="list-disc ml-5">
                @foreach ($loan->repayments as $repayment)
                    <li>{{ $repayment->date }} - {{ number_format($repayment->amount, 2) }}</li>
                @endforeach
            </ul>
        </div>

        <div class="p-4 rounded-lg bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-gray-800">
            <h3 class="font-semibold text-lg mb-3">{{ __('Contributions') }}</h3>
            <ul class="list-disc ml-5">
                @foreach ($loan->contributions as $contribution)
                    <li>{{ $contribution->user->name }} - {{ number_format($contribution->amount, 2) }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection