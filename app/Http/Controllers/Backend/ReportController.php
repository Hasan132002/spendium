<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Income;
use App\Traits\HasFamilyScope;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use HasFamilyScope;

    public function index(Request $request): Renderable|RedirectResponse
    {
        $user = Auth::user();
        $isHeadOrSuper = $this->isSuperadmin() || Family::where('father_id', $user->id)->exists();

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());
        $scope = $request->input('scope', $isHeadOrSuper ? 'family' : 'mine');

        if ($scope === 'family' && !$isHeadOrSuper) {
            $scope = 'mine';
        }

        $userIds = $scope === 'family'
            ? FamilyMember::whereIn('family_id', $this->familyIdsInScope())->pluck('user_id')->toArray()
            : [$user->id];

        // Pass a family variable for the view's scope toggle (just to know if family option should appear)
        $family = $isHeadOrSuper ? true : null;

        // Expenses by category
        $expensesByCategory = Expense::whereIn('user_id', $userIds)
            ->whereBetween('date', [$from, $to])
            ->with('category:id,name')
            ->get()
            ->groupBy(fn ($e) => $e->category?->name ?? 'Uncategorised')
            ->map(fn ($rows) => (float) $rows->sum('amount'))
            ->sortDesc();

        // Monthly trend (last 6 months)
        $trendStart = Carbon::parse($from)->subMonths(5)->startOfMonth();
        $trendEnd = Carbon::parse($to)->endOfMonth();

        $expenseTrend = Expense::whereIn('user_id', $userIds)
            ->whereBetween('date', [$trendStart, $trendEnd])
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $incomeTrend = Income::whereIn('user_id', $userIds)
            ->whereBetween('received_on', [$trendStart, $trendEnd])
            ->selectRaw('DATE_FORMAT(received_on, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Normalize to all months in range
        $period = [];
        $cursor = $trendStart->copy();
        while ($cursor <= $trendEnd) {
            $key = $cursor->format('Y-m');
            $period[$key] = [
                'income'  => (float) ($incomeTrend[$key] ?? 0),
                'expense' => (float) ($expenseTrend[$key] ?? 0),
            ];
            $cursor->addMonth();
        }

        // Totals
        $totalExpenses = Expense::whereIn('user_id', $userIds)
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $totalIncomes = Income::whereIn('user_id', $userIds)
            ->whereBetween('received_on', [$from, $to])
            ->sum('amount');

        $netSavings = $totalIncomes - $totalExpenses;

        return view('backend.pages.reports.index', compact(
            'from', 'to', 'scope', 'family',
            'expensesByCategory', 'period',
            'totalExpenses', 'totalIncomes', 'netSavings'
        ));
    }

    public function exportExpenses(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        $isHeadOrSuper = $this->isSuperadmin() || Family::where('father_id', $user->id)->exists();

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());
        $scope = $request->input('scope', $isHeadOrSuper ? 'family' : 'mine');

        if ($scope === 'family' && !$isHeadOrSuper) {
            $scope = 'mine';
        }

        $userIds = $scope === 'family'
            ? FamilyMember::whereIn('family_id', $this->familyIdsInScope())->pluck('user_id')->toArray()
            : [$user->id];

        $expenses = Expense::whereIn('user_id', $userIds)
            ->whereBetween('date', [$from, $to])
            ->with(['user:id,name', 'category:id,name'])
            ->orderBy('date', 'desc')
            ->get();

        $filename = 'expenses-' . $from . '-to-' . $to . '.csv';

        $output = fopen('php://temp', 'w+');
        fputcsv($output, ['Date', 'User', 'Category', 'Title', 'Amount', 'Approved', 'Note']);
        foreach ($expenses as $e) {
            fputcsv($output, [
                $e->date?->format('Y-m-d') ?? '',
                $e->user?->name ?? '',
                $e->category?->name ?? '',
                $e->title,
                number_format((float) $e->amount, 2, '.', ''),
                $e->approved ? 'Yes' : 'No',
                $e->note ?? '',
            ]);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportIncomes(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        $isHeadOrSuper = $this->isSuperadmin() || Family::where('father_id', $user->id)->exists();

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());
        $scope = $request->input('scope', $isHeadOrSuper ? 'family' : 'mine');

        $userIds = $scope === 'family'
            ? FamilyMember::whereIn('family_id', $this->familyIdsInScope())->pluck('user_id')->toArray()
            : [$user->id];

        $incomes = Income::whereIn('user_id', $userIds)
            ->whereBetween('received_on', [$from, $to])
            ->with('user:id,name')
            ->orderBy('received_on', 'desc')
            ->get();

        $filename = 'incomes-' . $from . '-to-' . $to . '.csv';

        $output = fopen('php://temp', 'w+');
        fputcsv($output, ['Date', 'User', 'Source', 'Title', 'Amount', 'Recurring', 'Note']);
        foreach ($incomes as $i) {
            fputcsv($output, [
                $i->received_on?->format('Y-m-d') ?? '',
                $i->user?->name ?? '',
                $i->source,
                $i->title,
                number_format((float) $i->amount, 2, '.', ''),
                $i->recurring ? ($i->recurrence_interval ?? 'yes') : 'no',
                $i->note ?? '',
            ]);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
