<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Goal, GoalContribution, Saving, SavingsTransaction, Budget, BudgetTransaction, Family, FamilyMember};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class GoalController extends Controller
{
    public function familyGoals(Request $request)
    {
        $user = Auth::user();
        $family = Family::where('father_id', $user->id)->first();

        if ($family) {
            // Father → show all family goals
            $goals = Goal::with(['user:id,name', 'contributions'])
                ->where('type', 'family')
                ->where('family_id', $family->id)
                ->get()
                ->map(function ($goal) {
                    $goal->collected_amount = $goal->contributions->sum('amount');
                    return $goal;
                });
        } else {
            // Mother/Child → show own family goals
            $goals = Goal::with('contributions')
                ->where('type', 'family')
                ->where('user_id', $user->id)
                ->get()
                ->map(function ($goal) {
                    $goal->collected_amount = $goal->contributions->sum('amount');
                    return $goal;
                });
        }

        return $this->success('Family goals retrieved', $goals);
    }

    public function createFamilyGoal(Request $request)
    {
        $user = Auth::user();
        $family = Family::where('father_id', $user->id)->first();

        if (!$family) {
            return $this->error('Only the father can create family goals.', null, 403);
        }

        $request->validate([
            'title' => [
                'required',
                'string',
                Rule::unique('goals')->where(function ($query) use ($family) {
                    return $query->where('family_id', $family->id)
                                ->where('type', 'family');
                }),
            ],
            'amount' => 'required|numeric|min:1',
        ]);


        $goal = Goal::create([
            'family_id'     => $family->id,
            'user_id'       => $user->id,
            'title'         => $request->title,
            'target_amount' => $request->amount,
            'type'          => 'family',
        ]);

        return $this->success('Family goal created', $goal);
    }

    public function myGoals(Request $request)
    {
        $user = Auth::user();
        $family = $user->familyMember?->family ?? Family::where('father_id', $user->id)->first();

        if ($family && $family->father_id === $user->id) {
            // Father → all members' personal goals
            $memberIds = FamilyMember::where('family_id', $family->id)->pluck('user_id')->toArray();
            $memberIds[] = $user->id;

            $goals = Goal::with(['user:id,name', 'contributions'])
                ->whereIn('user_id', $memberIds)
                ->where('type', 'personal')
                ->get()
                ->map(function ($goal) {
                    $goal->collected_amount = $goal->contributions->sum('amount');
                    return $goal;
                });
        } else {
            // Mother/Child → their own goals
            $goals = Goal::with('contributions')
                ->where('user_id', $user->id)
                ->where('type', 'personal')
                ->get()
                ->map(function ($goal) {
                    $goal->collected_amount = $goal->contributions->sum('amount');
                    return $goal;
                });
        }

        return $this->success('Personal goals retrieved', $goals);
    }

    public function createMyGoal(Request $request)
    {
       $request->validate([
        'title' => [
            'required',
            'string',
            Rule::unique('goals')->where(function ($query) {
                return $query->where('user_id', Auth::id())
                            ->where('type', 'personal');
            }),
        ],
        'amount' => 'required|numeric|min:1',
    ]);

    $familyId = Auth::user()->familyMember?->family_id ?? 1;


        $goal = Goal::create([
     'family_id'     => $familyId,
                 'user_id'       => Auth::id(),
            'title'         => $request->title,
            'target_amount' => $request->amount,
            'type'          => 'personal',
        ]);

        return $this->success('Personal goal created', $goal);
    }

    public function updateMyGoal(Request $request, $id)
    {
        $goal = Goal::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $goal->update($request->only('title', 'amount'));

        return $this->success('Goal updated', $goal);
    }

    public function deleteMyGoal($id)
    {
        $goal = Goal::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $goal->delete();

        return $this->success('Goal deleted');
    }

    public function contributeToGoal(Request $request)
    {
        $request->validate([
            'goal_id' => 'required|exists:goals,id',
            'amount'  => 'required|numeric|min:1'
        ]);

        $user = Auth::user();
        $goal = Goal::findOrFail($request->goal_id);

        $isGoalOwner = $goal->user_id === $user->id;
        $isFather = $goal->family && $goal->family->father_id === $user->id;

        if (!($isGoalOwner || $isFather)) {
            return $this->error('You are not allowed to contribute to this goal', null, 403);
        }

        $saving = Saving::where('user_id', $user->id)->first();

        if (!$saving || $saving->total < $request->amount) {
            return $this->error('Not enough savings to contribute', null, 422);
        }

        DB::transaction(function () use ($request, $saving, $goal, $user) {
            $saving->total -= $request->amount;
            $saving->save();

            $goal->saved_amount += $request->amount;
            $goal->save();

            GoalContribution::create([
                'goal_id' => $goal->id,
                'user_id' => $user->id,
                'amount'  => $request->amount,
            ]);

            SavingsTransaction::create([
                'user_id'   => $user->id,
                'action'    => 'deduct',
                'amount'    => $request->amount,
                'source'    => 'goal',
                'source_id' => $goal->id,
                'saving_id' => $saving->id,
            ]);
        });

        return $this->success('Contribution added to goal');
    }

    public function goalProgress($id)
    {
        $goal = Goal::with('contributions')->findOrFail($id);
        $contributed = $goal->contributions->sum('amount');
        $targetAmount = $goal->target_amount ?? 0;

        $progress = $targetAmount == 0 ? 0 : ($contributed / $targetAmount) * 100;

        return $this->success('Goal progress retrieved', [
            'goal' => $goal,
            'progress_percent' => round($progress, 2)
        ]);
    }
}
