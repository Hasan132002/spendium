@extends('layouts.auth')

@section('title', __('Family Invitation') . ' | ' . config('app.name'))

@section('content')
<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/50 shadow-theme-lg">
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('Accept Family Invitation') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            <strong class="text-gray-700 dark:text-gray-300">{{ $invitation->inviter->name }}</strong>
            {{ __('has invited you to join') }}
            <strong class="text-gray-700 dark:text-gray-300">{{ $invitation->family->name }}</strong>
            {{ __('as a') }} <strong class="capitalize text-gray-700 dark:text-gray-300">{{ $invitation->role }}</strong>.
        </p>
    </div>

    @if ($errors->any())
        <div class="mx-6 mt-4 rounded-lg border border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20 px-4 py-3 text-sm text-red-700 dark:text-red-300">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('family.invite.accept', $invitation->token) }}" class="p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Email') }}</label>
            <input type="text" value="{{ $invitation->email }}" disabled
                   class="h-11 w-full rounded-lg border border-gray-200 bg-gray-100 px-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
        </div>

        @if ($existingUser)
            <div class="rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-900/20 px-4 py-3 text-sm text-blue-700 dark:text-blue-300">
                {{ __('An account already exists for this email. Enter your current password to link this family to your account.') }}
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Your Password') }}</label>
                <input id="password" type="password" name="password" required
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>
        @else
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Your Name') }}</label>
                <input id="name" type="text" name="name" value="{{ old('name', $invitation->name) }}" required autofocus
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Set Password') }}</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>
        @endif

        <div class="pt-2">
            <button type="submit" class="btn-primary w-full">
                {{ $existingUser ? __('Link & Join Family') : __('Accept & Create Account') }}
            </button>
        </div>
    </form>
</div>
@endsection
