@extends('backend.layouts.app')

@section('title')
    {{ __('Dashboard') }} | {{ config('app.name') }}
@endsection

@section('before_vite_build')
    <script>
        var userGrowthData = @json($user_growth_data['data']);
        var userGrowthLabels = @json($user_growth_data['labels']);
        var trendLabels = @json($trend_labels);
        var trendIncome = @json($trend_income);
        var trendExpense = @json($trend_expense);
    </script>
@endsection

@php $currency = config('app.currency_symbol', '$'); @endphp

@section('admin-content')
<div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
                {{ __('Dashboard') }}
                @if ($is_super)
                    <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">{{ __('Superadmin — System-wide') }}</span>
                @endif
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('Overview of') }} {{ now()->format('F Y') }}</p>
        </div>
    </div>

    {{-- ROW 1: Finance KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-6">
        @can('family.member.invite')
            <a href="{{ route('admin.family.members.index') }}" class="block rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5 hover:shadow-theme-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">{{ __('Families') }}</span>
                    <i class="bi bi-house-heart text-lg text-blue-500"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['families_count']) }}</div>
                <p class="text-xs text-gray-500 mt-1">{{ number_format($stats['members_count']) }} {{ __('active members') }}</p>
            </a>
        @endcan

        <a href="{{ route('admin.incomes.my') }}" class="block rounded-2xl border border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/10 p-5 hover:shadow-theme-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-semibold text-green-700 dark:text-green-300">{{ __('Income (This Month)') }}</span>
                <i class="bi bi-arrow-down-circle text-lg text-green-600"></i>
            </div>
            <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $currency }}{{ number_format($stats['income_this_month'], 2) }}</div>
            <p class="text-xs text-green-600 mt-1">{{ __('View incomes →') }}</p>
        </a>

        <a href="{{ url('admin/expenses/my') }}" class="block rounded-2xl border border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/10 p-5 hover:shadow-theme-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-semibold text-red-700 dark:text-red-300">{{ __('Expenses (This Month)') }}</span>
                <i class="bi bi-arrow-up-circle text-lg text-red-600"></i>
            </div>
            <div class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $currency }}{{ number_format($stats['expense_this_month'], 2) }}</div>
            <p class="text-xs text-red-600 mt-1">{{ __('View expenses →') }}</p>
        </a>

        <a href="{{ url('admin/savings/my') }}" class="block rounded-2xl border border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-900/10 p-5 hover:shadow-theme-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-semibold text-blue-700 dark:text-blue-300">{{ __('Total Savings') }}</span>
                <i class="bi bi-piggy-bank-fill text-lg text-blue-600"></i>
            </div>
            <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $currency }}{{ number_format($stats['total_savings'], 2) }}</div>
            <p class="text-xs text-blue-600 mt-1">{{ __('View savings →') }}</p>
        </a>
    </div>

    {{-- ROW 2: Operational KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-6">
        <a href="{{ url('admin/fund-request/funds/all') }}" class="block rounded-2xl border border-yellow-200 bg-yellow-50 dark:border-yellow-900 dark:bg-yellow-900/10 p-5 hover:shadow-theme-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-semibold text-yellow-700 dark:text-yellow-300">{{ __('Pending Requests') }}</span>
                <i class="bi bi-envelope-check text-lg text-yellow-600"></i>
            </div>
            <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ number_format($stats['pending_fund_requests']) }}</div>
            <p class="text-xs text-yellow-600 mt-1">{{ __('Needs review →') }}</p>
        </a>

        <a href="{{ url('admin/loans') }}" class="block rounded-2xl border border-orange-200 bg-orange-50 dark:border-orange-900 dark:bg-orange-900/10 p-5 hover:shadow-theme-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-semibold text-orange-700 dark:text-orange-300">{{ __('Active Loans') }}</span>
                <i class="bi bi-cash-coin text-lg text-orange-600"></i>
            </div>
            <div class="text-2xl font-bold text-orange-700 dark:text-orange-300">{{ number_format($stats['active_loans']) }}</div>
            <p class="text-xs text-orange-600 mt-1">{{ __('Outstanding') }}: {{ $currency }}{{ number_format($stats['outstanding_loans'], 2) }}</p>
        </a>

        <a href="{{ url('admin/goals/family') }}" class="block rounded-2xl border border-purple-200 bg-purple-50 dark:border-purple-900 dark:bg-purple-900/10 p-5 hover:shadow-theme-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-semibold text-purple-700 dark:text-purple-300">{{ __('Active Goals') }}</span>
                <i class="bi bi-bullseye text-lg text-purple-600"></i>
            </div>
            <div class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ number_format($stats['active_family_goals'] + $stats['active_personal_goals']) }}</div>
            <p class="text-xs text-purple-600 mt-1">{{ $stats['active_family_goals'] }} {{ __('family') }} · {{ $stats['active_personal_goals'] }} {{ __('personal') }}</p>
        </a>

        <a href="{{ route('admin.notifications.index') }}" class="block rounded-2xl border border-pink-200 bg-pink-50 dark:border-pink-900 dark:bg-pink-900/10 p-5 hover:shadow-theme-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs uppercase font-semibold text-pink-700 dark:text-pink-300">{{ __('Unread Notifications') }}</span>
                <i class="bi bi-bell text-lg text-pink-600"></i>
            </div>
            <div class="text-2xl font-bold text-pink-700 dark:text-pink-300">{{ number_format($stats['unread_notifications']) }}</div>
            <p class="text-xs text-pink-600 mt-1">{{ __('Open notification center →') }}</p>
        </a>
    </div>

    {{-- ROW 3: Chart + Quick Actions --}}
    <div class="grid grid-cols-12 gap-4 md:gap-6 mb-6">
        {{-- Income vs Expense trend --}}
        <div class="col-span-12 lg:col-span-8 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Income vs Expense — Last 6 Months') }}</h3>
                <a href="{{ route('admin.reports.index') }}" class="text-xs text-blue-600 hover:underline">{{ __('Full report →') }}</a>
            </div>
            <div id="income-expense-trend" style="min-height: 280px;"></div>
        </div>

        {{-- Quick Links + Monthly Snapshot --}}
        <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] p-5">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90 mb-4">{{ __('Quick Actions') }}</h3>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('admin.incomes.create') }}" class="flex flex-col items-center justify-center py-4 rounded-xl bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 transition-colors">
                    <i class="bi bi-plus-circle text-2xl text-green-600 mb-1"></i>
                    <span class="text-xs font-medium text-green-700 dark:text-green-300">{{ __('Record Income') }}</span>
                </a>
                <a href="{{ url('admin/fund-request/my') }}" class="flex flex-col items-center justify-center py-4 rounded-xl bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900/20 dark:hover:bg-yellow-900/30 transition-colors">
                    <i class="bi bi-envelope-paper text-2xl text-yellow-600 mb-1"></i>
                    <span class="text-xs font-medium text-yellow-700 dark:text-yellow-300">{{ __('Fund Request') }}</span>
                </a>
                <a href="{{ url('admin/categories/all') }}" class="flex flex-col items-center justify-center py-4 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 transition-colors">
                    <i class="bi bi-tags text-2xl text-blue-600 mb-1"></i>
                    <span class="text-xs font-medium text-blue-700 dark:text-blue-300">{{ __('Categories') }}</span>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="flex flex-col items-center justify-center py-4 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/20 dark:hover:bg-purple-900/30 transition-colors">
                    <i class="bi bi-bar-chart-line text-2xl text-purple-600 mb-1"></i>
                    <span class="text-xs font-medium text-purple-700 dark:text-purple-300">{{ __('Reports') }}</span>
                </a>
            </div>

            <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
                <p class="text-xs uppercase font-semibold text-gray-500 mb-2">{{ __('Monthly Snapshot') }}</p>
                <div class="flex justify-between items-center py-1.5 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Family Budget Pool') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $currency }}{{ number_format($stats['family_budget_balance'], 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-1.5 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Net (Income − Expense)') }}</span>
                    <span class="font-semibold {{ $stats['income_this_month'] - $stats['expense_this_month'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $currency }}{{ number_format($stats['income_this_month'] - $stats['expense_this_month'], 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ROW 4: Recent Activity --}}
    <div class="grid grid-cols-12 gap-4 md:gap-6 mb-6">
        <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Recent Fund Requests') }}</h3>
                <a href="{{ url('admin/fund-request/funds/all') }}" class="text-xs text-blue-600 hover:underline">{{ __('All →') }}</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($recent_fund_requests as $req)
                    <div class="px-5 py-3 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $req->user?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $req->category?->name ?? '—' }} · {{ $req->created_at?->diffForHumans() }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold">{{ $currency }}{{ number_format((float) $req->amount, 0) }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full capitalize
                                    @if ($req->status === 'approved') bg-green-100 text-green-700
                                    @elseif ($req->status === 'rejected') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700 @endif">{{ $req->status }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No recent fund requests.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Recent Expenses') }}</h3>
                <a href="{{ url('admin/expenses/family') }}" class="text-xs text-blue-600 hover:underline">{{ __('All →') }}</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($recent_expenses as $exp)
                    <div class="px-5 py-3 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $exp->title }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $exp->user?->name ?? '—' }} · {{ $exp->category?->name ?? '—' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold text-red-600">-{{ $currency }}{{ number_format((float) $exp->amount, 0) }}</p>
                                <p class="text-xs text-gray-400">{{ $exp->date?->format('d M') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No recent expenses.') }}</p>
                @endforelse
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ __('Recent Incomes') }}</h3>
                <a href="{{ route('admin.incomes.my') }}" class="text-xs text-blue-600 hover:underline">{{ __('All →') }}</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($recent_incomes as $inc)
                    <div class="px-5 py-3 hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $inc->title }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $inc->user?->name ?? '—' }} · {{ ucfirst($inc->source) }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold text-green-600">+{{ $currency }}{{ number_format((float) $inc->amount, 0) }}</p>
                                <p class="text-xs text-gray-400">{{ $inc->received_on?->format('d M') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500">{{ __('No recent incomes.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- System Administration (Superadmin only) --}}
    @can('user.view')
    <div class="mt-6">
        <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 mb-3">{{ __('System Administration') }}</p>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4 md:gap-6">
            @include('backend.pages.dashboard.partials.card', [
                'icon_svg' => asset('images/icons/user.svg'),
                'label' => __('Users'),
                'value' => $total_users,
                'bg' => '#635BFF',
                'class' => 'bg-white',
                'url' => route('admin.users.index'),
            ])
            @include('backend.pages.dashboard.partials.card', [
                'icon_svg' => asset('images/icons/key.svg'),
                'label' => __('Roles'),
                'value' => $total_roles,
                'bg' => '#00D7FF',
                'class' => 'bg-white',
                'url' => route('admin.roles.index'),
            ])
            @include('backend.pages.dashboard.partials.card', [
                'icon' => 'bi bi-shield-check',
                'label' => __('Permissions'),
                'value' => $total_permissions,
                'bg' => '#FF4D96',
                'class' => 'bg-white',
                'url' => route('admin.roles.index'),
            ])
            @include('backend.pages.dashboard.partials.card', [
                'icon' => 'bi bi-translate',
                'label' => __('Translations'),
                'value' => $languages['total'] . ' / ' . $languages['active'],
                'bg' => '#22C55E',
                'class' => 'bg-white',
                'url' => route('admin.translations.index'),
            ])
        </div>

        <div class="mt-6 grid grid-cols-12 gap-4 md:gap-6">
            <div class="col-span-12 md:col-span-8">
                @include('backend.pages.dashboard.partials.user-growth')
            </div>
            <div class="col-span-12 md:col-span-4">
                @include('backend.pages.dashboard.partials.user-history')
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof trendLabels !== 'undefined' && document.querySelector('#income-expense-trend')) {
            new ApexCharts(document.querySelector('#income-expense-trend'), {
                chart: { type: 'area', height: 280, toolbar: { show: false } },
                series: [
                    { name: 'Income', data: trendIncome },
                    { name: 'Expense', data: trendExpense },
                ],
                colors: ['#10b981', '#ef4444'],
                xaxis: { categories: trendLabels, labels: { style: { fontSize: '12px' } } },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0, stops: [0, 90, 100] } },
                dataLabels: { enabled: false },
                legend: { position: 'top', horizontalAlign: 'right' },
                tooltip: { y: { formatter: function (v) { return '{{ $currency }}' + Number(v).toLocaleString(); } } },
            }).render();
        }
    });
</script>
@endpush
