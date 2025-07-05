@extends('backend.layouts.app')

@section('title')
    {{ __('Edit Role') }} | {{ config('app.name') }}
@endsection

@section('admin-content')

<div class="p-4 mx-auto max-w-[var(--breakpoint-2xl)] md:p-6">
    <div x-data="{ pageName: '{{ __('Edit Role') }}' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ __('Edit Role') }}</h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.dashboard') }}">
                            {{ __('Home') }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ route('admin.roles.index') }}">
                            {{ __('Roles') }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90">{{ __('Edit Role') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
        @method('PUT')
        @csrf
        <div class="space-y-8">
            @include('backend.layouts.partials.messages')
            <!-- Role Details Section -->
            <div class="rounded-lg border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        {{ __('Role Details') }}
                    </h3>
                    <div class="flex gap-4">
                        <button type="submit" class="btn-primary">
                            {{ __('Save') }}
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn-default">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ __('Role Name') }}
                            </label>
                            <input required autofocus name="name" value="{{ $role->name }}" type="text" placeholder="{{ __('Enter a Role Name') }}" class="mt-2 form-control">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Permissions Section -->
            <div class="rounded-lg border border-gray-200 bg-white shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        {{ __('Permissions') }}
                    </h3>
                </div>
                <div class="p-4">
                    <div class="mb-4">
                        <input type="checkbox" id="checkPermissionAll" class="mr-2" {{ $roleService->roleHasPermissions($role, $all_permissions) ? 'checked' : '' }}>
                        <label for="checkPermissionAll" class="text-sm text-gray-700 dark:text-gray-400">
                            {{ __('Select All') }}
                        </label>
                    </div>
                    <hr class="mb-6">
                    @php $i = 1; @endphp
                    @foreach ($permission_groups as $group)
                    <div class="mb-6">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="group{{ $i }}Management" class="mr-2" {{ $roleService->roleHasPermissions($role, $roleService->getPermissionsByGroupName($group->name)) ? 'checked' : '' }}>
                            <label for="group{{ $i }}Management" class="capitalize text-sm font-medium text-gray-700 dark:text-gray-400">
                                {{ ucfirst($group->name) }}
                            </label>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 xl:grid-cols-6 gap-4" data-group="group{{ $i }}Management">
                            @php
                                $permissions = $roleService->getPermissionsByGroupName($group->name);
                            @endphp
                            @foreach ($permissions as $permission)
                            <div>
                                <input type="checkbox" id="checkPermission{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}" class="mr-2" 
                                       {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                <label for="checkPermission{{ $permission->id }}" class="capitalize text-sm text-gray-700 dark:text-gray-400">
                                    {{ $permission->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @php $i++; @endphp
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-start gap-4">
                <button type="submit" class="btn-primary">
                    {{ __('Save') }}
                </button>
                <a href="{{ route('admin.roles.index') }}" class="btn-default">
                    {{ __('Cancel') }}
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
    @include('backend.pages.roles.partials.scripts')
@endpush
