<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\Saving;
use App\Models\SavingsTransaction;
use App\Models\User;
use App\Notifications\GoalContributionAdded;
use App\Traits\HasFamilyScope;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminGoalController extends Controller
{
    use HasFamilyScope;

    public function createFamily(): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.goal.manage']);

        $families = $this->familiesInScope();
        if ($families->isEmpty()) {
            return redirect()->route('admin.dashboard')->with('error', 'No family found.');
        }

        return view('dashboard.goals.create-family', compact('families'));
    }

    public function storeFamilyGoal(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.goal.manage']);

        $data = $request->validate([
            'family_id'     => ['required', 'integer', 'exists:families,id'],
            'title'         => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'target_date'   => ['nullable', 'date', 'after:today'],
        ]);

        Goal::create([
            'family_id'     => $data['family_id'],
            'user_id'       => Auth::id(),
            'title'         => $data['title'],
            'target_amount' => $data['target_amount'],
            'saved_amount'  => 0,
            'target_date'   => $data['target_date'] ?? null,
            'type'          => 'family',
            'status'        => 'active',
        ]);

        return redirect('/admin/goals/family')->with('success', 'Family goal created.');
    }

    public function createPersonal(): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.goal.manage']);

        return view('dashboard.goals.create-personal');
    }

    public function storePersonalGoal(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.goal.manage']);

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'target_date'   => ['nullable', 'date', 'after:today'],
        ]);

        $familyId = Auth::user()->familyMember?->family_id;

        Goal::create([
            'family_id'     => $familyId,
            'user_id'       => Auth::id(),
            'title'         => $data['title'],
            'target_amount' => $data['target_amount'],
            'saved_amount'  => 0,
            'target_date'   => $data['target_date'] ?? null,
            'type'          => 'personal',
            'status'        => 'active',
        ]);

        return redirect('/admin/goals/personal')->with('success', 'Goal created.');
    }

    public function contribute(Request $request, int $goalId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.goal.manage']);

        $goal = Goal::findOrFail($goalId);
        $user = Auth::user();

        $isGoalOwner = $goal->user_id === $user->id;
        $isFather = $goal->family && $goal->family->father_id === $user->id;

        if (!($isGoalOwner || $isFather || $this->isSuperadmin())) {
            return back()->with('error', 'Not allowed to contribute to this goal.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $saving = Saving::firstOrCreate(['user_id' => $user->id], ['total' => 0]);

        if ((float) $saving->total < (float) $data['amount']) {
            return back()->with('error', 'Not enough savings balance. Add to savings first.');
        }

        $contribution = null;
        DB::transaction(function () use ($data, $saving, $goal, $user, &$contribution) {
            $saving->total -= $data['amount'];
            $saving->save();

            $goal->saved_amount += $data['amount'];
            $goal->save();

            $contribution = GoalContribution::create([
                'goal_id' => $goal->id,
                'user_id' => $user->id,
                'amount'  => $data['amount'],
            ]);

            SavingsTransaction::create([
                'saving_id' => $saving->id,
                'user_id'   => $user->id,
                'type'      => 'transfer_to_goal',
                'amount'    => $data['amount'],
                'note'      => 'Transferred to goal: ' . $goal->title,
            ]);
        });

        if ($contribution && $goal->user_id && $goal->user_id !== $user->id) {
            User::find($goal->user_id)?->notify(new GoalContributionAdded($contribution));
        }

        return back()->with('success', 'Contribution added to goal.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $goal = Goal::findOrFail($id);

        if ($goal->user_id !== Auth::id() && !$this->isSuperadmin()) {
            return back()->with('error', 'You can only delete your own goals.');
        }

        $goal->delete();

        return back()->with('success', 'Goal deleted.');
    }
}
