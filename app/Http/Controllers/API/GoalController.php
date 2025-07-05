<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Goal, GoalContribution, Saving, SavingsTransaction, Budget, BudgetTransaction, Family,FamilyMember};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoalController extends Controller {
  public function familyGoals(Request $request)
{
    $user = Auth::user();

    // Check if user is father of a family
    $family = Family::where('father_id', $user->id)->first();

    if ($family) {
        // 👨‍👧‍👦 Father → show all family goals of the family
        $goals = Goal::with(['user:id,name', 'contributions'])
            ->where('type', 'family')
            ->where('family_id', $family->id)
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });

    } else {
        // 👩‍👦 Mother/Child → only show family goals created by themselves
        $goals = Goal::with('contributions')
            ->where('type', 'family')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });
    }

    return response()->json($goals);
}


    public function createFamilyGoal(Request $request)
{
    $user = Auth::user();

    // Check if user is father in any family
    $family = Family::where('father_id', $user->id)->first();

    // If not father, block access
    if (!$family) {
        return response()->json(['error' => 'Only the father can create family goals.'], 403);
    }

    // Validate input
    $request->validate([
        'title' => 'required|string',
        'amount' => 'required|numeric|min:1'
    ]);

    // Create goal
    $goal = Goal::create([
        'family_id'     => $family->id,
        'user_id'       => $user->id,
        'title'         => $request->title,
        'target_amount' => $request->amount,
        'type'          => 'family',
    ]);

    return response()->json(['message' => 'Family goal created', 'goal' => $goal]);
}


   public function myGoals(Request $request)
{
    $user = Auth::user();
    $family = $user->familyMember?->family ?? Family::where('father_id', $user->id)->first();

    if ($family && $family->father_id === $user->id) {
        // Father → show all members' personal goals + his own
        $memberIds = FamilyMember::where('family_id', $family->id)->pluck('user_id')->toArray();
        $memberIds[] = $user->id; // include father

        $goals = Goal::with(['user:id,name', 'contributions'])
            ->whereIn('user_id', $memberIds)
            ->where('type', 'personal')
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });

    } else {
        // Mother/Child → only their personal goals
        $goals = Goal::with('contributions')
            ->where('user_id', $user->id)
            ->where('type', 'personal')
            ->get()
            ->map(function ($goal) {
                $goal->collected_amount = $goal->contributions->sum('amount');
                return $goal;
            });
    }

    return response()->json($goals);
}




    public function createMyGoal(Request $request) {
        $request->validate([
            'title' => 'required|string',
            'amount' => 'required|numeric|min:1'
        ]);

        $goal = Goal::create([
        'family_id' => Auth::user()->familyMember?->family_id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'target_amount' => $request->amount,
            'type' => 'personal',
        ]);

        return response()->json(['message' => 'Personal goal created', 'goal' => $goal]);
    }

    public function updateMyGoal(Request $request, $id) {
        $goal = Goal::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $goal->update($request->only('title', 'amount'));
        return response()->json(['message' => 'Goal updated', 'goal' => $goal]);
    }

    public function deleteMyGoal($id) {
        $goal = Goal::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $goal->delete();
        return response()->json(['message' => 'Goal deleted']);
    }

   public function contributeToGoal(Request $request) {
    $request->validate([
        'goal_id' => 'required|exists:goals,id',
        'amount' => 'required|numeric|min:1'
    ]);

    $user = Auth::user();
    $goal = Goal::findOrFail($request->goal_id);

    $isGoalOwner = $goal->user_id === $user->id;


    $isFather = $goal->family && $goal->family->father_id === $user->id;

    if (!($isGoalOwner || $isFather)) {
        return response()->json(['error' => 'You are not allowed to contribute to this goal'], 403);
    }

    $saving = Saving::where('user_id', $user->id)->first();
// dd($saving);
    if (!$saving || $saving->total  < $request->amount) {
        return response()->json(['error' => 'Not enough savings to contribute'], 422);
    }

    DB::transaction(function () use ($request, $saving, $goal, $user) {
        $saving->total -= $request->amount;
        $saving->save();

         $goal->saved_amount += $request->amount;
    $goal->save();

        GoalContribution::create([
            'goal_id' => $goal->id,
            'user_id' => $user->id,
            'amount' => $request->amount,
        ]);

        SavingsTransaction::create([
            'user_id' => $user->id,
            'action' => 'deduct',
            'amount' => $request->amount,
            'source' => 'goal',
            'source_id' => $goal->id,
             'saving_id' => $saving->id,
        ]);
    });

    return response()->json(['message' => 'Contribution added to goal']);
}


   public function goalProgress($id) {
    $goal = Goal::with('contributions')->findOrFail($id);
    $contributed = $goal->contributions->sum('amount');

    $targetAmount = $goal->target_amount ?? 0;

    if ($targetAmount == 0) {
        $progress = 0;
    } else {
        $progress = ($contributed / $targetAmount) * 100;
    }

    return response()->json([
        'goal' => $goal,
        'progress_percent' => round($progress, 2)
    ]);
}

}
