<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" href="{{ config('settings.site_favicon') ?? asset('favicon.ico') }}" type="image/x-icon">

    @viteReactRefresh
    @vite(['resources/js/app.js', 'resources/css/app.css'], 'build')
</head>
<body
    x-data="{
        darkMode: JSON.parse(localStorage.getItem('darkMode') ?? 'false')
    }"
    x-init="
        $watch('darkMode', value => {
            localStorage.setItem('darkMode', JSON.stringify(value));
            if (value) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); }
        });
        if (darkMode) { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); }
    "
    class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">

    <div class="min-h-screen flex flex-col">
        {{-- Top bar with logo + dark toggle --}}
        <header class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="/images/logo/spendium-removebg-preview.png" alt="{{ config('app.name') }}" class="h-8">
            </a>

            <button type="button"
                    @click="darkMode = !darkMode"
                    class="relative flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white transition-colors">
                <i class="bi bi-moon-stars" x-show="!darkMode"></i>
                <i class="bi bi-sun" x-show="darkMode" style="display:none"></i>
            </button>
        </header>

        {{-- Content area --}}
        <main class="flex-1 flex items-center justify-center px-4 py-10">
            <div class="w-full max-w-xl">
                @if (session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20 px-4 py-3 text-sm text-green-700 dark:text-green-300">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('info'))
                    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-900/20 px-4 py-3 text-sm text-blue-700 dark:text-blue-300">
                        {{ session('info') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="px-6 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
            &copy; {{ now()->year }} {{ config('app.name') }}
        </footer>
    </div>
</body>
</html>
