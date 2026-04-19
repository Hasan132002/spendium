@extends('layouts.auth')

@section('title', __('Login') . ' | ' . config('app.name'))

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/50 shadow-theme-lg">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Welcome back') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Sign in to your Spendium account.') }}</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="p-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('E-Mail Address') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 @error('email') border-red-500 @enderror">
            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('password') border-red-500 @enderror">
            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                {{ __('Remember me') }}
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-brand-500 hover:underline">{{ __('Forgot password?') }}</a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="btn-primary w-full">{{ __('Login') }}</button>
        </div>

        @if (Route::has('register'))
            <p class="text-center text-sm text-gray-600 dark:text-gray-400 pt-2">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register') }}" class="text-brand-500 hover:underline">{{ __('Create your family') }}</a>
            </p>
        @endif
    </form>
</div>
@endsection
