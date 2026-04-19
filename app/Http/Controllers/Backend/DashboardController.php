<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\FundRequest;
use App\Models\Goal;
use App\Models\Income;
use App\Models\Loan;
use App\Models\Saving;
use App\Models\User;
use App\Services\Charts\UserChartService;
use App\Services\LanguageService;
use App\Traits\HasFamilyScope;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    use HasFamilyScope;

    public function __construct(
        private readonly UserChartService $userChartService,
        private readonly LanguageService $languageService
    ) {
    }

    public function index()
    {
        $this->checkAuthorization(auth()->user(), ['dashboard.view']);

        $user = Auth::user();
        $isSuper = $this->isSuperadmin();
        $familyIds = $this->familyIdsInScope();
        $memberIds = !empty($familyIds)
            ? FamilyMember::whereIn('family_id', $familyIds)->pluck('user_id')->all()
            : [$user->id];

        $currentMonth = now()->format('Y-m');

        $stats = [
            'families_count'        => Family::count(),
            'members_count'         => $isSuper
                ? FamilyMember::where('status', 'accepted')->count()
                : FamilyMember::whereIn('family_id', $familyIds ?: [0])->where('status', 'accepted')->count(),
            'income_this_month'     => (float) Income::whereIn('user_id', $memberIds)
                ->whereYear('received_on', now()->year)
                ->whereMonth('received_on', now()->month)
                ->sum('amount'),
            'expense_this_month'    => (float) Expense::whereIn('user_id', $memberIds)
                ->whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->sum('amount'),
            'pending_fund_requests' => FundRequest::whereIn('family_id', $familyIds ?: [0])
                ->where('status', 'pending')
                ->count(),
            'active_loans'          => Loan::whereIn('family_id', $familyIds ?: [0])
                ->whereIn('status', ['pending', 'partially_paid'])
                ->count(),
            'outstanding_loans'     => (float) Loan::whereIn('family_id', $familyIds ?: [0])
                ->whereIn('status', ['pending', 'partially_paid'])
                ->sum('remaining_amount'),
            'total_savings'         => (float) Saving::whereIn('user_id', $memberIds)->sum('total'),
            'active_family_goals'   => Goal::whereIn('family_id', $familyIds ?: [0])
                ->where('type', 'family')
                ->where('status', 'active')
                ->count(),
            'active_personal_goals' => Goal::whereIn('user_id', $memberIds)
                ->where('type', 'personal')
                ->where('status', 'active')
                ->count(),
            'unread_notifications'  => $user->unreadNotifications()->count(),
            'family_budget_balance' => (float) Budget::whereIn('family_id', $familyIds ?: [0])
                ->where('type', 'family')
                ->where('month', $currentMonth)
                ->sum('amount'),
        ];

        $recentExpenses = Expense::whereIn('user_id', $memberIds)
            ->with(['user:id,name', 'category:id,name'])
            ->latest('date')
            ->limit(5)
            ->get();

        $recentFundRequests = FundRequest::whereIn('family_id', $familyIds ?: [0])
            ->with(['user:id,name', 'category:id,name'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $recentIncomes = Income::whereIn('user_id', $memberIds)
            ->with('user:id,name')
            ->latest('received_on')
            ->limit(5)
            ->get();

        // 6-month income vs expense trend
        $trendStart = now()->subMonths(5)->startOfMonth();
        $expenseTrend = Expense::whereIn('user_id', $memberIds)
            ->whereBetween('date', [$trendStart, now()->endOfMonth()])
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')->orderBy('month')->pluck('total', 'month')->toArray();

        $incomeTrend = Income::whereIn('user_id', $memberIds)
            ->whereBetween('received_on', [$trendStart, now()->endOfMonth()])
            ->selectRaw('DATE_FORMAT(received_on, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')->orderBy('month')->pluck('total', 'month')->toArray();

        $trendLabels = [];
        $trendIncome = [];
        $trendExpense = [];
        $cursor = $trendStart->copy();
        while ($cursor <= now()) {
            $key = $cursor->format('Y-m');
            $trendLabels[] = $cursor->format('M Y');
            $trendIncome[] = (float) ($incomeTrend[$key] ?? 0);
            $trendExpense[] = (float) ($expenseTrend[$key] ?? 0);
            $cursor->addMonth();
        }

        return view('backend.pages.dashboard.index', [
            'is_super'             => $isSuper,
            'stats'                => $stats,
            'recent_expenses'      => $recentExpenses,
            'recent_fund_requests' => $recentFundRequests,
            'recent_incomes'       => $recentIncomes,
            'trend_labels'         => $trendLabels,
            'trend_income'         => $trendIncome,
            'trend_expense'        => $trendExpense,
            'total_users'          => number_format(User::count()),
            'total_roles'          => number_format(Role::count()),
            'total_permissions'    => number_format(Permission::count()),
            'languages'            => [
                'total'  => number_format(count($this->languageService->getLanguages())),
                'active' => number_format(count($this->languageService->getActiveLanguages())),
            ],
            'user_growth_data'     => $this->userChartService->getUserGrowthData(
                request()->get('chart_filter_period', 'last_12_months')
            )->getData(true),
            'user_history_data'    => $this->userChartService->getUserHistoryData(),
        ]);
    }
}
