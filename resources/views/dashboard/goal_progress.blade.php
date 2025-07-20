@extends('backend.layouts.app')

@section('title')
    {{ __('Goal Progress') }} | {{ config('app.name') }}
@endsection

@section('admin-content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <div x-data="{ pageName: '{{ __('Goal Progress') }}' }">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ __('Goal Progress') }}
            </h2>
            <nav>
                <ol class="flex items-center gap-1.5">
                    <li>
                        <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400"
                           href="{{ route('admin.dashboard') }}">
                            {{ __('Home') }}
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <li class="text-sm text-gray-800 dark:text-white/90">
                        {{ __('Goal Progress') }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="px-5 py-4 sm:px-6 sm:py-5">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white">
                    {{ $goal->title ?? __('Goal Details') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('Target Amount') }}: <strong>{{ number_format($goal->target_amount) }}</strong><br>
                    {{ __('Collected Amount') }}: <strong>{{ number_format($contributed) }}</strong><br>
                    {{ __('Progress') }}: <strong>{{ round($progress, 2) }}%</strong>
                </p>

                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                        <div class="bg-green-500 h-4 rounded-full" style="width: {{ round($progress, 2) }}%"></div>
                    </div>
                </div>

                @if ($goal->contributions->count())
                    <div class="mt-6">
                        <h4 class="text-md font-semibold text-gray-800 dark:text-white mb-2">{{ __('Contributions') }}</h4>
                        <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                            @foreach ($goal->contributions as $contribution)
                                <li class="flex justify-between border-b pb-2">
                                    <span>{{ $contribution->created_at->format('d M Y') }}</span>
                                    <span>{{ number_format($contribution->amount) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">{{ __('No contributions yet.') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
