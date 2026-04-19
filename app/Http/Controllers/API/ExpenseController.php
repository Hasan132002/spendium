<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Budget;
use App\Models\BudgetTransaction;
use App\Models\User;
use App\Notifications\ExpenseLogged;
use App\Notifications\ExpenseApproved;
use App\Notifications\BudgetThresholdReached;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'budget_id'   => 'required|exists:budgets,id',
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string',
            'amount'      => 'required|numeric',
            'date'        => 'required|date',
            'note'        => 'nullable|string',
            'receipt'     => 'nullable|image|max:4096',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $budget = Budget::find($request->budget_id);

        if (!$budget) {
            return $this->error('Budget not found', null, 400);
        }

        if ($budget->user_id !== null && $budget->user_id != auth()->id()) {
            return $this->error('Unauthorized to use this budget', null, 403);
        }

        if ($budget->user_id === null) {
            $isMember = FamilyMember::where('family_id', $budget->family_id)
                ->where('user_id', auth()->id())
                ->exists();

            if (!$isMember) {
                return $this->error('Not a member of this family', null, 403);
            }
        }

        if ($budget->amount < $request->amount) {
            return $this->error('Insufficient budget assigned', null, 403);
        }

        $budget->amount -= $request->amount;
        $budget->save();

        $expense = Expense::create([
            'user_id'      => auth()->id(),
            'budget_id'    => $budget->id,
            'category_id'  => $request->category_id,
            'title'        => $request->title,
            'amount'       => $request->amount,
            'note'         => $request->note,
            'date'         => $request->date,
            'approved'     => false,
            'receipt_path' => $receiptPath,
        ]);

        BudgetTransaction::create([
            'budget_id' => $budget->id,
            'user_id'   => auth()->id(),
            'action'    => 'deduct',
            'amount'    => $request->amount,
            'source'    => 'expense',
            'source_id' => $expense->id,
        ]);

        // Notify family head
        if ($budget->family_id) {
            $family = Family::find($budget->family_id);
            if ($family && $family->father_id !== auth()->id()) {
                User::find($family->father_id)?->notify(new ExpenseLogged($expense));
            }
        }

        // Budget threshold notifications (80% / 100%)
        if ($budget->initial_amount > 0) {
            $used = $budget->initial_amount - $budget->amount;
            $percent = (int) floor(($used / $budget->initial_amount) * 100);
            if ($percent >= 100) {
                auth()->user()->notify(new BudgetThresholdReached($budget, $percent));
            } elseif ($percent >= 80) {
                auth()->user()->notify(new BudgetThresholdReached($budget, $percent));
            }
        }

        return $this->success('Expense submitted', $expense->load('category', 'budget'));
    }

    public function myExpenses()
    {
        $expenses = Expense::where('user_id', auth()->id())
            ->with(['category:id,name', 'budget'])
            ->get();

        return $this->success('My expenses fetched', $expenses);
    }

    public function approveExpense($id)
    {
        $expense = Expense::findOrFail($id);

        $family = Family::where('father_id', auth()->id())->first();

        if (!$family) {
            return $this->error('Family not found', null, 400);
        }

        $member = FamilyMember::where('family_id', $family->id)
            ->where('user_id', $expense->user_id)
            ->first();

        if (!$member) {
            return $this->error('Not authorized to approve this expense', null, 403);
        }

        $expense->approved = true;
        $expense->save();

        $expense->user?->notify(new ExpenseApproved($expense));

        return $this->success('Expense approved', $expense);
    }

    public function familyExpenses()
    {
        $family = Family::where('father_id', auth()->id())->firstOrFail();

        $memberIds = FamilyMember::where('family_id', $family->id)->pluck('user_id');

        $expenses = Expense::whereIn('user_id', $memberIds)
            ->with(['user:id,name', 'category:id,name', 'budget'])
            ->get();

        return $this->success('Family expenses fetched', $expenses);
    }
}
