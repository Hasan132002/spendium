@extends('layouts.auth')

@section('title', __('Register') . ' | ' . config('app.name'))

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/50 shadow-theme-lg">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Create Your Family Account') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Sign up as a family head to manage your household finances.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="p-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Your Name') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('name') border-red-500 @enderror">
            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('email') border-red-500 @enderror">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Register as') }}</label>
                <select id="role" name="role" required
                        class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('role') border-red-500 @enderror">
                    <option value="">{{ __('-- Select --') }}</option>
                    <option value="father" {{ old('role') === 'father' ? 'selected' : '' }}>{{ __('Father (Head)') }}</option>
                    <option value="mother" {{ old('role') === 'mother' ? 'selected' : '' }}>{{ __('Mother (Head)') }}</option>
                </select>
                @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="family_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Family Name') }}</label>
                <input id="family_name" type="text" name="family_name" value="{{ old('family_name') }}" required
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('family_name') border-red-500 @enderror">
                @error('family_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Children and other members must be invited by a family head.') }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Password') }}</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('password') border-red-500 @enderror">
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Confirm Password') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>
        </div>

        <div class="pt-2">
            <button type="submit" class="btn-primary w-full">{{ __('Register & Create Family') }}</button>
        </div>

        <p class="text-center text-sm text-gray-600 dark:text-gray-400 pt-2">
            {{ __('Already have an account?') }}
            <a href="{{ route('admin.login') }}" class="text-brand-500 hover:underline">{{ __('Log in') }}</a>
        </p>
    </form>
</div>
@endsection
