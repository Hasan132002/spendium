<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\{Goal, GoalContribution, Saving, SavingsTransaction, Budget, BudgetTransaction, Family};

class SavingsController extends Controller {
    public function mySavings() {
        $saving = Saving::firstOrCreate(['user_id' => Auth::id()], ['amount' => 0]);
        return response()->json($saving);
    }

   public function addToSavings(Request $request) {
    $request->validate(['amount' => 'required|numeric|min:1']);

    $user = Auth::user();
    $amount = $request->amount;

    // Update or create user's saving
    $saving = Saving::firstOrCreate(['user_id' => $user->id]);
    $saving->total += $amount;
    $saving->save();

    // Create savings transaction
    SavingsTransaction::create([
            'saving_id' => $saving->id,

        'user_id' => $user->id,
        'action' => 'add',
        'amount' => $amount,
        'source' => 'manual',
    ]);

    // Also update Budget and BudgetTransaction
$month = now()->format('Y-m');
$familyId = $user->familyMember?->family_id;

if (!$familyId) {
    return response()->json(['error' => 'Family ID not found for user'], 422);
}    $budget = Budget::firstOrCreate(
        ['user_id' => $user->id, 'month' => $month,'family_id' => $familyId,],
        ['amount' => 0]
    );

    $budget->amount += $amount;
    $budget->save();

    BudgetTransaction::create([
        'budget_id' => $budget->id,
        'amount' => $amount,
        'category' => 'manual_saving', // optional category
        'note' => 'Manual saving added to budget',
    ]);

    return response()->json(['message' => 'Amount added to savings and budget']);
}


    public function savingsHistory() {
        $history = SavingsTransaction::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return response()->json($history);
    }

    public function transferToGoal(Request $request) {
        $request->validate([
            'goal_id' => 'required|exists:goals,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $saving = Saving::firstOrCreate(['user_id' => Auth::id()]);

        if ($saving->amount < $request->amount) {
            return response()->json(['error' => 'Insufficient savings'], 422);
        }

        $saving->amount -= $request->amount;
        $saving->save();

        GoalContribution::create([
            'goal_id' => $request->goal_id,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
        ]);

        SavingsTransaction::create([
            'user_id' => Auth::id(),
            'action' => 'deduct',
            'amount' => $request->amount,
            'source' => 'goal',
            'source_id' => $request->goal_id
        ]);

        return response()->json(['message' => 'Transferred to goal']);
    }

    public function endOfMonthRollover() {
        $user = Auth::user();
        $month = Carbon::now()->format('Y-m');

        $budgets = Budget::where('user_id', $user->id)->where('month', $month)->get();
        $totalRemaining = 0;

        foreach ($budgets as $budget) {
            $used = BudgetTransaction::where('budget_id', $budget->id)->sum('amount');
            $remaining = $budget->amount - $used;
            if ($remaining > 0) {
                $totalRemaining += $remaining;
            }
        }

        if ($totalRemaining > 0) {
            $saving = Saving::firstOrCreate(['user_id' => $user->id]);
            $saving->amount += $totalRemaining;
            $saving->save();

            SavingsTransaction::create([
                'user_id' => $user->id,
                'action' => 'add',
                'amount' => $totalRemaining,
                'source' => 'rollover',
            ]);
        }

        return response()->json(['message' => 'Rollover complete', 'amount' => $totalRemaining]);
    }
}
