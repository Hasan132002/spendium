@extends('backend.layouts.app')

@section('title', __('New Fund Request') . ' | ' . config('app.name'))

@section('admin-content')
<div class="p-4 mx-auto max-w-3xl md:p-6">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Request Funds') }}</h2>
        <a href="{{ url('admin/fund-request/my') }}" class="btn-default">{{ __('Back') }}</a>
    </div>

    @include('backend.layouts.partials.messages')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <form action="{{ route('admin.fund-requests.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Category') }} *</label>
                    <select name="category_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Amount') }} *</label>
                    <input type="number" step="0.01" name="amount" required value="{{ old('amount') }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Note / Justification') }}</label>
                <textarea name="note" rows="3" placeholder="Briefly explain why you need this fund..."
                          class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('note') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Submit Request') }}</button>
                <a href="{{ url('admin/fund-request/my') }}" class="btn-default">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
