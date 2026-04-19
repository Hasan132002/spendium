@extends('backend.layouts.app')

@section('title', __('Log Expense') . ' | ' . config('app.name'))

@section('admin-content')
<div class="p-4 mx-auto max-w-3xl md:p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Log New Expense') }}</h2>
        <a href="{{ url('admin/expenses/my') }}" class="btn-default">{{ __('Back') }}</a>
    </div>

    @include('backend.layouts.partials.messages')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <form action="{{ route('admin.expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Against Budget') }} *</label>
                <select name="budget_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @foreach ($budgets as $b)
                        <option value="{{ $b->id }}" data-category="{{ $b->category_id }}">
                            {{ $b->category?->name ?? 'Budget' }} — {{ config('app.currency_symbol', '$') }}{{ number_format((float) $b->amount, 2) }} remaining
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Title') }} *</label>
                    <input type="text" name="title" required value="{{ old('title') }}" placeholder="e.g. Weekly Groceries"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Amount') }} *</label>
                    <input type="number" step="0.01" name="amount" required value="{{ old('amount') }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Category') }} *</label>
                    <select name="category_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Date') }} *</label>
                    <input type="date" name="date" required value="{{ old('date', now()->toDateString()) }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Note') }}</label>
                <textarea name="note" rows="2" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('note') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Receipt (optional)') }}</label>
                <input type="file" name="receipt" accept="image/*"
                       class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <p class="text-xs text-gray-500 mt-1">{{ __('Upload a photo of your bill/invoice (max 4MB).') }}</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Save Expense') }}</button>
                <a href="{{ url('admin/expenses/my') }}" class="btn-default">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
