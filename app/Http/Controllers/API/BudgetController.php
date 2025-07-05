<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Budget, Family, Expense, FundRequest, FamilyMember, User, Category,BudgetTransaction};
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function createFamilyBudget(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric'
        ]);

        $family = Family::where('father_id', auth()->id())->firstOrFail();

       $budget = Budget::create([
    'family_id'      => $family->id,
    'amount'         => $request->amount,
    'initial_amount' => $request->amount,
    'type'           => 'family',
    'month'          => now()->format('Y-m'),
]);
BudgetTransaction::create([
    'budget_id' => $budget->id,
    'user_id' => auth()->id(),
    'action' => 'add',
    'amount' => $request->amount,
    'source' => 'top_up'
]);


        return response()->json(['message' => 'Family monthly budget created', 'budget' => $budget]);
    }

   public function assignToMember(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'category_id' => 'required|exists:categories,id',
        'amount' => 'required|numeric'
    ]);

    $fatherId = auth()->id();

    // 1. Get logged-in father's family
    $family = Family::where('father_id', $fatherId)->firstOrFail();

    // 2. Check if user is a member of this family
    $isMember = FamilyMember::where('family_id', $family->id)
                            ->where('user_id', $request->user_id)
                            ->exists();

    if (!$isMember) {
        return response()->json(['error' => 'User is not a member of your family.'], 403);
    }

    // 3. Assign budget
    $budget = Budget::create([
        'family_id'   => $family->id,
        'user_id'     => $request->user_id,
        // 'category_id'    => Category::findOrFail($request->category_id)->name, // assuming `category` is string in budgets table
        'category_id'    => $request->category_id, // assuming `category` is string in budgets table
        'amount'      => $request->amount,
    ]);

    return response()->json(['message' => 'Budget assigned to member', 'budget' => $budget]);
}
public function familyBudget()
{
    $family = Family::where('father_id', auth()->id())->first();

    if (!$family) {
        return response()->json([
            'error' => 'No family found for this user.'
        ], 404);
    }

    $budgets = Budget::where('family_id', $family->id)->get();
    return response()->json($budgets);
}


public function assignedBudgets()
{
    $user = auth()->user();

    // Check if father
    $family = Family::where('father_id', $user->id)->first();

    if ($family) {
        // Father → Show all budgets for family
        $budgets = Budget::with(['user:id,name,email', 'category:id,name'])
            ->where('family_id', $family->id)
            ->whereNotNull('user_id') // only assigned budgets
            ->get();
    } else {
        // Member (mother/child) → Show own budgets
        $budgets = Budget::with('category:id,name')
            ->where('user_id', $user->id)
            ->get();
    }

    return response()->json($budgets);
}
}