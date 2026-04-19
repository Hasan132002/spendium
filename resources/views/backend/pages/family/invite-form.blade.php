@extends('backend.layouts.app')

@section('title')
    {{ __('Invite Family Member') }} - {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
            {{ __('Invite Member to') }} {{ $family->name }}
        </h2>
        <a href="{{ route('admin.family.members.index') }}" class="btn-default">
            {{ __('Back to Members') }}
        </a>
    </div>

    @include('backend.layouts.partials.messages')

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="p-5 sm:p-6">
            <form action="{{ route('admin.family.members.store-invite') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Name (optional)') }}</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                               class="mt-1 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Email') }} *</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}"
                               class="mt-1 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-400">{{ __('Role') }} *</label>
                        <select name="role" id="role" required
                                class="mt-1 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="mother" {{ old('role') === 'mother' ? 'selected' : '' }}>{{ __('Mother') }}</option>
                            <option value="child" {{ old('role') === 'child' ? 'selected' : '' }}>{{ __('Child') }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-400 mb-3">
                        {{ __('Family Permissions (optional)') }}
                    </h3>
                    <p class="text-xs text-gray-500 mb-3">{{ __('Personal permissions are included automatically. Select which family-wide permissions this member should have.') }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach ($familyPermissions as $permission)
                            <label class="flex items-center text-sm">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}"
                                       {{ in_array($permission, old('permissions', [])) ? 'checked' : '' }}
                                       class="h-4 w-4 mr-2 text-brand-500 border-gray-300 rounded">
                                {{ $permission }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">{{ __('Send Invitation') }}</button>
                    <a href="{{ route('admin.family.members.index') }}" class="btn-default">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
