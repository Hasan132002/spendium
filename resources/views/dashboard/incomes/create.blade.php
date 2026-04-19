@extends('backend.layouts.app')

@section('title')
    {{ __('Record Income') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-3xl md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('Record New Income') }}
        </h2>
        <a href="{{ route('admin.incomes.my') }}" class="btn-default">{{ __('Back') }}</a>
    </div>

    @include('backend.layouts.partials.messages')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <form action="{{ route('admin.incomes.store') }}" method="POST" class="space-y-5"
              x-data="{ recurring: {{ old('recurring', 0) }} ? true : false }">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Title') }} *</label>
                    <input type="text" name="title" required value="{{ old('title') }}" placeholder="e.g., October Salary"
                           class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    @error('title') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Amount') }} *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required value="{{ old('amount') }}"
                           class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    @error('amount') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Source') }} *</label>
                    <select name="source" required class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @foreach (['salary','business','freelance','rental','investment','gift','other'] as $src)
                            <option value="{{ $src }}" {{ old('source', 'salary') === $src ? 'selected' : '' }}>{{ ucfirst($src) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Received On') }} *</label>
                    <input type="date" name="received_on" required value="{{ old('received_on', now()->toDateString()) }}"
                           class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Note (optional)') }}</label>
                <textarea name="note" rows="2" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('note') }}</textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="recurring" value="1" x-model="recurring" class="h-4 w-4 text-brand-500 rounded border-gray-300">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('This is a recurring income') }}</span>
                </label>

                <div x-show="recurring" x-transition class="mt-3" style="display: none">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">{{ __('Repeats') }}</label>
                    <select name="recurrence_interval" class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="monthly" {{ old('recurrence_interval', 'monthly') === 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
                        <option value="weekly" {{ old('recurrence_interval') === 'weekly' ? 'selected' : '' }}>{{ __('Weekly') }}</option>
                        <option value="yearly" {{ old('recurrence_interval') === 'yearly' ? 'selected' : '' }}>{{ __('Yearly') }}</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('Save Income') }}</button>
                <a href="{{ route('admin.incomes.my') }}" class="btn-default">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
