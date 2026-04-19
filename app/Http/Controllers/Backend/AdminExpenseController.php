<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetTransaction;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;
use App\Notifications\BudgetThresholdReached;
use App\Notifications\ExpenseApproved;
use App\Notifications\ExpenseLogged;
use App\Traits\HasFamilyScope;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminExpenseController extends Controller
{
    use HasFamilyScope;

    public function create(): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.expense.manage']);

        $month = now()->format('Y-m');
        $budgets = Budget::where('user_id', Auth::id())
            ->where('month', $month)
            ->with('category:id,name')
            ->get();

        if ($budgets->isEmpty()) {
            return redirect()->route('admin.dashboard')->with('error', 'You have no assigned budgets this month to log expenses against.');
        }

        $categories = Category::whereNull('user_id')->orWhere('user_id', Auth::id())->get();

        return view('dashboard.expenses.create', compact('budgets', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.expense.manage']);

        $data = $request->validate([
            'budget_id'   => ['required', 'integer', 'exists:budgets,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title'       => ['required', 'string', 'max:255'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'date'        => ['required', 'date'],
            'note'        => ['nullable', 'string'],
            'receipt'     => ['nullable', 'image', 'max:4096'],
        ]);

        $budget = Budget::findOrFail($data['budget_id']);

        if ($budget->user_id !== null && $budget->user_id !== Auth::id()) {
            return back()->with('error', 'You cannot log expense against someone else\'s budget.');
        }

        if ((float) $budget->amount < (float) $data['amount']) {
            return back()->with('error', 'Insufficient budget balance.');
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = null;
        DB::transaction(function () use ($data, $budget, $receiptPath, &$expense) {
            $budget->amount -= $data['amount'];
            $budget->save();

            $expense = Expense::create([
                'user_id'      => Auth::id(),
                'budget_id'    => $budget->id,
                'category_id'  => $data['category_id'],
                'title'        => $data['title'],
                'amount'       => $data['amount'],
                'note'         => $data['note'] ?? null,
                'date'         => $data['date'],
                'approved'     => false,
                'receipt_path' => $receiptPath,
            ]);

            BudgetTransaction::create([
                'budget_id' => $budget->id,
                'user_id'   => Auth::id(),
                'action'    => 'deduct',
                'amount'    => $data['amount'],
                'source'    => 'expense',
                'source_id' => $expense->id,
            ]);
        });

        // Notify head
        if ($budget->family_id) {
            $family = Family::find($budget->family_id);
            if ($family && $family->father_id !== Auth::id()) {
                User::find($family->father_id)?->notify(new ExpenseLogged($expense));
            }
        }

        // Budget threshold
        if ($budget->initial_amount > 0) {
            $used = $budget->initial_amount - $budget->amount;
            $percent = (int) floor(($used / $budget->initial_amount) * 100);
            if ($percent >= 80) {
                Auth::user()->notify(new BudgetThresholdReached($budget, $percent));
            }
        }

        return redirect('/admin/expenses/my')->with('success', 'Expense logged.');
    }

    public function approve(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.expense.approve']);

        $expense = Expense::findOrFail($id);

        if (!$this->isSuperadmin()) {
            $family = Family::where('father_id', Auth::id())->first();
            if (!$family) {
                return back()->with('error', 'Only family head can approve.');
            }

            $member = FamilyMember::where('family_id', $family->id)->where('user_id', $expense->user_id)->exists();
            if (!$member) {
                return back()->with('error', 'Not authorized.');
            }
        }

        $expense->approved = true;
        $expense->save();

        $expense->user?->notify(new ExpenseApproved($expense));

        return back()->with('success', 'Expense approved.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $expense = Expense::findOrFail($id);

        if ($expense->user_id !== Auth::id() && !$this->isSuperadmin()) {
            return back()->with('error', 'You can only delete your own expenses.');
        }

        // Refund the budget
        if ($expense->budget_id) {
            $budget = Budget::find($expense->budget_id);
            if ($budget) {
                $budget->amount += $expense->amount;
                $budget->save();
            }
        }

        BudgetTransaction::where('source', 'expense')->where('source_id', $expense->id)->delete();
        $expense->delete();

        return back()->with('success', 'Expense deleted and budget refunded.');
    }
}
