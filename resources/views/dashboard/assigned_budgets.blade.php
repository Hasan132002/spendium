@extends('backend.layouts.app')

@section('title')
    {{ __('Assigned Budgets') }} | {{ config('app.name') }}
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div x-data="{ pageName: '{{ __('Assigned Budgets') }}' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ __('Assigned Budgets') }}
            </h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                            {{ __('Home') }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90">{{ __('Assigned Budgets') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Budgets Table -->
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Assigned Budgets') }}</h3>

                @include('backend.partials.search-form', [
                    'placeholder' => __('Search by category or user'),
                ])
            </div>

            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                @include('backend.layouts.partials.messages')

                <table class="w-full dark:text-gray-400">
                    <thead class="bg-light text-capitalize">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Sl') }}</th>
                            <th class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Category') }}</th>
                            <th class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Amount') }}</th>
                            <th class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Assigned To') }}</th>
                            <th class="p-2 bg-gray-50 dark:bg-gray-800 dark:text-white text-left px-5">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($budgets as $index => $budget)
                            <tr class="{{ $loop->index + 1 != count($budgets) ?  'border-b border-gray-100 dark:border-gray-800' : '' }}">
                                <td class="px-5 py-4 sm:px-6">{{ $index + 1 }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    {{ $budget->category->name ?? __('N/A') }}
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    {{ number_format($budget->amount, 2) }}
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    {{ $budget->user->name ?? __('Self') }}
                                    <br>
                                    <small class="text-xs text-gray-500">{{ $budget->user->email ?? '' }}</small>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    {{ $budget->created_at->format('d M, Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    {{ __('No assigned budgets found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
