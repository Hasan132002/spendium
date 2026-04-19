@extends('backend.layouts.app')

@section('title', __('Create Family Budget') . ' | ' . config('app.name'))

@section('admin-content')
<div class="p-4 mx-auto max-w-3xl md:p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Create Family Budget') }}</h2>
        <a href="{{ url('admin/budget/family') }}" class="btn-default">{{ __('Back') }}</a>
    </div>

    @include('backend.layouts.partials.messages')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <form action="{{ route('admin.budget.store-family') }}" method="POST" class="space-y-5">
            @csrf

            @if ($families->count() > 1)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Family') }}</label>
                    <select name="family_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach ($families as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif ($families->count() === 1)
                <input type="hidden" name="family_id" value="{{ $families->first()->id }}">
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Family') }}: <strong>{{ $families->first()->name }}</strong></p>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Month') }} *</label>
                    <input type="month" name="month" required value="{{ old('month', now()->format('Y-m')) }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Amount') }} *</label>
                    <input type="number" step="0.01" name="amount" required value="{{ old('amount') }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Create Budget') }}</button>
                <a href="{{ url('admin/budget/family') }}" class="btn-default">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
