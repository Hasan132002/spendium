<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetTransaction;
use App\Models\Category;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Traits\HasFamilyScope;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminBudgetController extends Controller
{
    use HasFamilyScope;

    /** Show form to create a family-level monthly budget (family head). */
    public function createFamily(): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.budget.create']);

        $family = Family::where('father_id', Auth::id())->first();
        if (!$family && !$this->isSuperadmin()) {
            return redirect()->route('admin.dashboard')->with('error', 'Only the family head can create a family budget.');
        }

        $families = $this->familiesInScope();

        return view('dashboard.budgets.create-family', compact('families'));
    }

    public function storeFamily(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.budget.create']);

        $data = $request->validate([
            'family_id' => ['nullable', 'integer', 'exists:families,id'],
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'month'     => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $familyId = $data['family_id'] ?? Family::where('father_id', Auth::id())->value('id');
        if (!$familyId) {
            return back()->with('error', 'Family could not be determined.');
        }

        $existing = Budget::where('family_id', $familyId)
            ->where('type', 'family')
            ->where('month', $data['month'])
            ->first();

        if ($existing) {
            return back()->with('error', "Family budget already exists for {$data['month']}.");
        }

        DB::transaction(function () use ($familyId, $data) {
            $budget = Budget::create([
                'family_id'      => $familyId,
                'user_id'        => null,
                'category_id'    => null,
                'amount'         => $data['amount'],
                'initial_amount' => $data['amount'],
                'type'           => 'family',
                'month'          => $data['month'],
            ]);

            BudgetTransaction::create([
                'budget_id' => $budget->id,
                'user_id'   => Auth::id(),
                'action'    => 'add',
                'amount'    => $data['amount'],
                'source'    => 'top_up',
            ]);
        });

        return redirect()->route('admin.budget.family')->with('success', "Family budget for {$data['month']} created.");
    }

    /** Show form to assign a sub-budget to a family member. */
    public function createAssigned(): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.budget.assign']);

        $families = $this->familiesInScope();
        if ($families->isEmpty()) {
            return redirect()->route('admin.dashboard')->with('error', 'No family found.');
        }

        $categories = Category::whereNull('user_id')->orderBy('name')->get();

        return view('dashboard.budgets.create-assigned', compact('families', 'categories'));
    }

    public function storeAssigned(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.budget.assign']);

        $data = $request->validate([
            'family_id'   => ['required', 'integer', 'exists:families,id'],
            'user_id'     => ['required', 'integer', 'exists:users,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'month'       => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        if (!$this->isSuperadmin()) {
            $family = Family::where('father_id', Auth::id())->first();
            if (!$family || $family->id !== (int) $data['family_id']) {
                return back()->with('error', 'Not authorized for this family.');
            }
        }

        $memberExists = FamilyMember::where('family_id', $data['family_id'])
            ->where('user_id', $data['user_id'])
            ->exists();
        if (!$memberExists) {
            return back()->with('error', 'User is not a member of this family.');
        }

        $mainBudget = Budget::where('family_id', $data['family_id'])
            ->where('type', 'family')
            ->where('month', $data['month'])
            ->first();

        if ($mainBudget && $mainBudget->amount < $data['amount']) {
            return back()->with('error', 'Main family budget does not have enough remaining amount.');
        }

        DB::transaction(function () use ($data, $mainBudget) {
            $memberBudget = Budget::create([
                'family_id'      => $data['family_id'],
                'user_id'        => $data['user_id'],
                'category_id'    => $data['category_id'],
                'amount'         => $data['amount'],
                'initial_amount' => $data['amount'],
                'type'           => 'assigned',
                'month'          => $data['month'],
            ]);

            if ($mainBudget) {
                $mainBudget->amount -= $data['amount'];
                $mainBudget->save();

                BudgetTransaction::create([
                    'budget_id' => $mainBudget->id,
                    'user_id'   => Auth::id(),
                    'action'    => 'deduct',
                    'amount'    => $data['amount'],
                    'source'    => 'assign_to_member',
                    'source_id' => $memberBudget->id,
                ]);
            }

            BudgetTransaction::create([
                'budget_id' => $memberBudget->id,
                'user_id'   => Auth::id(),
                'action'    => 'add',
                'amount'    => $data['amount'],
                'source'    => 'assigned',
            ]);
        });

        return redirect()->route('admin.budget.assigned')->with('success', 'Budget assigned successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $budget = Budget::findOrFail($id);

        if (!$this->isSuperadmin()) {
            $family = Family::where('father_id', Auth::id())->first();
            if (!$family || $budget->family_id !== $family->id) {
                return back()->with('error', 'Not authorized.');
            }
        }

        BudgetTransaction::where('budget_id', $budget->id)->delete();
        $budget->delete();

        return back()->with('success', 'Budget deleted.');
    }
}
