@extends('backend.layouts.app')

@section('title')
    {{ __('My Contributions') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-7xl md:p-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90 mb-6">{{ __('My Contributions') }}</h2>
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-left">{{ __('#') }}</th>
                        <th class="px-5 py-3 text-left">{{ __('Loan') }}</th>
                        <th class="px-5 py-3 text-left">{{ __('Contributor') }}</th>
                        <th class="px-5 py-3 text-left">{{ __('Amount') }}</th>
                        <th class="px-5 py-3 text-left">{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contributions as $index => $contribution)
                        <tr class="border-t border-gray-100 dark:border-gray-800">
                            <td class="px-5 py-3">{{ $index + 1 }}</td>
                            <td class="px-5 py-3">{{ $contribution->loan->category->name }}</td>
                            <td class="px-5 py-3">{{ $contribution->user->name }}</td>
                            <td class="px-5 py-3">{{ number_format($contribution->amount, 2) }}</td>
                            <td class="px-5 py-3">{{ $contribution->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
