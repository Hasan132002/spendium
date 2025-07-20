<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Budget, Family, Expense, FundRequest, FamilyMember, User, Category, BudgetTransaction};
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
            'user_id'   => auth()->id(),
            'action'    => 'add',
            'amount'    => $request->amount,
            'source'    => 'top_up'
        ]);

        return $this->success('Family monthly budget created', $budget);
    }

    public function assignToMember(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'amount'      => 'required|numeric'
        ]);

        $fatherId = auth()->id();

        $family = Family::where('father_id', $fatherId)->firstOrFail();

        $isMember = FamilyMember::where('family_id', $family->id)
                                ->where('user_id', $request->user_id)
                                ->exists();

        if (!$isMember) {
            return $this->error('User is not a member of your family.', null, 403);
        }

        $budget = Budget::create([
            'family_id'   => $family->id,
            'user_id'     => $request->user_id,
            'category_id' => $request->category_id,
            'amount'      => $request->amount,
        ]);

        return $this->success('Budget assigned to member', $budget);
    }

    public function familyBudget()
    {
        $family = Family::where('father_id', auth()->id())->first();

        if (!$family) {
            return $this->error('No family found for this user.', null, 400);
        }

        $budgets = Budget::where('family_id', $family->id)->get();
        return $this->success('Family budgets fetched', $budgets);
    }

    public function assignedBudgets()
    {
        $user = auth()->user();

        $family = Family::where('father_id', $user->id)->first();

        if ($family) {
            $budgets = Budget::with(['user:id,name,email', 'category:id,name'])
                ->where('family_id', $family->id)
                ->whereNotNull('user_id')
                ->get();
        } else {
            $budgets = Budget::with('category:id,name')
                ->where('user_id', $user->id)
                ->get();
        }

        return $this->success('Assigned budgets fetched', $budgets);
    }
}
