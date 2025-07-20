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

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="px-5 py-4 sm:px-6 sm:py-5">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">{{ __('Current Saving') }}</h3>
            <div class="mt-4 text-2xl font-semibold text-green-600 dark:text-green-400">
                {{ number_format($saving->amount, 2) }} {{ config('app.currency_symbol', '$') }}
            </div>
        </div>
    </div>
</div>
@endsection
