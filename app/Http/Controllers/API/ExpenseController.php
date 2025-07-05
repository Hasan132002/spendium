<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\Models\Expense;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Budget;
use App\Models\BudgetTransaction;

class ExpenseController extends Controller
{
  public function store(Request $request)
{
    $request->validate([
        'budget_id' => 'required|exists:budgets,id',
        'category_id' => 'required|exists:categories,id',
        'title' => 'required|string',
        'amount' => 'required|numeric',
        'date' => 'required|date',
        'note' => 'nullable|string'
    ]);

    $budget = Budget::find($request->budget_id);

    if (!$budget) {
        return response()->json(['error' => 'Budget not found'], 404);
    }

    // Case 1: User assigned
    if ($budget->user_id !== null && $budget->user_id != auth()->id()) {
        return response()->json(['error' => 'Unauthorized to use this budget'], 403);
    }

    // Case 2: Family-level budget
    if ($budget->user_id === null) {
        $isMember = \App\Models\FamilyMember::where('family_id', $budget->family_id)
                    ->where('user_id', auth()->id())
                    ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'Not a member of this family'], 403);
        }
    }

    if ($budget->amount < $request->amount) {
        return response()->json(['error' => 'Insufficient budget assigned'], 403);
    }
$budget->amount -= $request->amount;
$budget->save();

    $expense = Expense::create([
        'user_id'     => auth()->id(),
        'budget_id'   => $budget->id,
        'category_id' => $request->category_id,
        'title'       => $request->title,
        'amount'      => $request->amount,
        'note'        => $request->note,
        'date'        => $request->date,
        'approved'    => false
    ]);
    BudgetTransaction::create([
    'budget_id' => $budget->id,
    'user_id' => auth()->id(),
    'action' => 'deduct',
    'amount' => $request->amount,
    'source' => 'expense',
    'source_id' => $expense->id,
]);


    return response()->json([
        'message' => 'Expense submitted',
        'expense' => $expense->load('category', 'budget')
    ]);
}


  public function myExpenses()
{
    $expenses = Expense::where('user_id', auth()->id())
        ->with(['category:id,name', 'budget'])
        ->get();

    return response()->json($expenses);
}


    public function approveExpense($id)
{
    $expense = Expense::findOrFail($id);

    $family = Family::where('father_id', auth()->id())->first();

    if (!$family) {
        return response()->json(['error' => 'Family not found'], 404);
    }

    $member = FamilyMember::where('family_id', $family->id)
                ->where('user_id', $expense->user_id)
                ->first();

    if (!$member) {
        return response()->json(['error' => 'Not authorized to approve this expense'], 403);
    }

    $expense->approved = true;
    $expense->save();

    return response()->json(['message' => 'Expense approved']);
}

    public function familyExpenses()
{
    $family = Family::where('father_id', auth()->id())->firstOrFail();

    $memberIds = FamilyMember::where('family_id', $family->id)->pluck('user_id');

    $expenses = Expense::whereIn('user_id', $memberIds)
        ->with(['user:id,name', 'category:id,name', 'budget'])
        ->get();

    return response()->json($expenses);
}

}