@extends('backend.layouts.app')

@section('title', __('Assign Budget') . ' | ' . config('app.name'))

@section('admin-content')
<div class="p-4 mx-auto max-w-3xl md:p-6"
     x-data="{
        familyId: '{{ old('family_id', $families->first()?->id) }}',
        get members() {
            const fams = @js($families->map(fn($f) => ['id' => $f->id, 'members' => $f->members->map(fn($m) => ['user_id' => $m->user_id, 'name' => $m->user?->name ?? '—', 'role' => $m->role])->values()])->keyBy('id')->all());
            return fams[this.familyId]?.members ?? [];
        }
     }">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Assign Budget to Member') }}</h2>
        <a href="{{ url('admin/budget/assigned') }}" class="btn-default">{{ __('Back') }}</a>
    </div>

    @include('backend.layouts.partials.messages')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-6">
        <form action="{{ route('admin.budget.store-assigned') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Family') }} *</label>
                    <select name="family_id" x-model="familyId" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach ($families as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Member') }} *</label>
                    <select name="user_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <template x-for="m in members" :key="m.user_id">
                            <option :value="m.user_id" x-text="m.name + ' (' + m.role + ')'"></option>
                        </template>
                    </select>
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Month') }} *</label>
                    <input type="month" name="month" required value="{{ old('month', now()->format('Y-m')) }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Amount') }} *</label>
                    <input type="number" step="0.01" name="amount" required value="{{ old('amount') }}"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Assign Budget') }}</button>
                <a href="{{ url('admin/budget/assigned') }}" class="btn-default">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
