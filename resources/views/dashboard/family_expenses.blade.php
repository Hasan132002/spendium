@extends('backend.layouts.app')

@section('title')
    {{ __('Family Expenses') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div>
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ __('Family Expenses') }}
            </h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                            {{ __('Home') }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90">{{ __('Family Expenses') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5 flex justify-between items-center">
                <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ __('Family Expenses') }}</h3>
                {{-- Add search or filter if needed --}}
            </div>

            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 overflow-x-auto">
                @include('backend.layouts.partials.messages')

                <table id="dataTable" class="w-full dark:text-gray-400">
                    <thead class="bg-light text-capitalize">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left">{{ __('Sl') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Member Name') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Category') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Budget Title') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Amount') }}</th>
                            <th class="px-5 py-3 text-left">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $index => $expense)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4">{{ $index + 1 }}</td>
                                <td class="px-5 py-4">{{ $expense->user->name ?? '-' }}</td>
                                <td class="px-5 py-4">{{ $expense->category->name ?? '-' }}</td>
                                <td class="px-5 py-4">{{ $expense->budget->title ?? '-' }}</td>
                                <td class="px-5 py-4">{{ number_format($expense->amount, 2) }}</td>
                                <td class="px-5 py-4">{{ $expense->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center px-5 py-4">
                                    <p class="text-gray-500 dark:text-gray-400">{{ __('No expenses found') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Pagination if used --}}
                {{-- <div class="my-4 px-4 sm:px-6">
                    {{ $expenses->links() }}
                </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection
