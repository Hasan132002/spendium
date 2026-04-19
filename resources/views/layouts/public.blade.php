<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('Community') . ' | ' . config('app.name'))</title>
    <link rel="icon" href="{{ config('settings.site_favicon') ?? asset('favicon.ico') }}" type="image/x-icon">

    @viteReactRefresh
    @vite(['resources/js/app.js', 'resources/css/app.css'], 'build')
</head>
<body
    x-data="{ darkMode: JSON.parse(localStorage.getItem('darkMode') ?? 'false') }"
    x-init="
        $watch('darkMode', v => {
            localStorage.setItem('darkMode', JSON.stringify(v));
            if (v) document.documentElement.classList.add('dark'); else document.documentElement.classList.remove('dark');
        });
        if (darkMode) document.documentElement.classList.add('dark');
    "
    class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">

    <nav class="sticky top-0 z-20 border-b border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ url('/community') }}" class="flex items-center gap-3">
                <img src="/images/logo/spendium-removebg-preview.png" alt="{{ config('app.name') }}" class="h-8">
                <span class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Community') }}</span>
            </a>
            <div class="flex items-center gap-3">
                <button type="button" @click="darkMode = !darkMode"
                        class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                    <i class="bi bi-moon-stars" x-show="!darkMode"></i>
                    <i class="bi bi-sun" x-show="darkMode" style="display:none"></i>
                </button>
                @auth
                    <a href="{{ url('/admin') }}" class="btn-primary text-sm">{{ __('My Dashboard') }}</a>
                @else
                    <a href="{{ route('admin.login') }}" class="text-sm text-gray-700 dark:text-gray-300 hover:underline">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="btn-primary text-sm">{{ __('Sign Up') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 dark:border-gray-800 py-6 text-center text-xs text-gray-500 dark:text-gray-400">
        &copy; {{ now()->year }} {{ config('app.name') }} &mdash; {{ __('Family finance, smarter.') }}
    </footer>
</body>
</html>
