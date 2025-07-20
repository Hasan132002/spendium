@extends('backend.layouts.app')

@section('title')
    {{ __('Family Budgets') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            {{ __('Family Budgets') }}
        </h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                        {{ __('Home') }}
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li class="text-sm text-gray-800 dark:text-white">{{ __('Family Budgets') }}</li>
            </ol>
        </nav>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center">
                <h3 class="text-base font-medium text-gray-800 dark:text-white">
                    {{ __('All Family Budgets') }}
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">#</th>
                            <th scope="col" class="px-6 py-3">{{ __('User') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Family') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Category') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Amount') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('Month') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budgets as $key => $budget)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <td class="px-6 py-4">{{ $key + 1 }}</td>
                                <td class="px-6 py-4">{{ $budget->user->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $budget->family->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $budget->category->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ number_format($budget->amount, 2) }}</td>
                                <td class="px-6 py-4">{{ $budget->month }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    {{ __('No budgets found.') }}
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
