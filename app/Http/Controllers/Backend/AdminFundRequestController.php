<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetTransaction;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Family;
use App\Models\FundRequest;
use App\Models\User;
use App\Notifications\FundRequestApproved;
use App\Notifications\FundRequestDeclined;
use App\Notifications\FundRequestSubmitted;
use App\Traits\HasFamilyScope;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminFundRequestController extends Controller
{
    use HasFamilyScope;

    public function create(): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.fund_request.create']);

        $user = Auth::user();
        $familyMember = $user->familyMember;
        if (!$familyMember) {
            return redirect()->route('admin.dashboard')->with('error', 'You must be in a family to request funds.');
        }

        if ($familyMember->role === 'father') {
            return redirect()->route('admin.dashboard')->with('error', 'Family heads can create budgets directly.');
        }

        $categories = Category::whereNull('user_id')->orWhere('user_id', $user->id)->get();

        return view('dashboard.fund_requests.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.fund_request.create']);

        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'note'        => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $familyMember = $user->familyMember;
        if (!$familyMember || $familyMember->role === 'father') {
            return back()->with('error', 'Not authorized.');
        }

        $fund = FundRequest::create([
            'user_id'     => $user->id,
            'family_id'   => $familyMember->family_id,
            'category_id' => $data['category_id'],
            'amount'      => $data['amount'],
            'note'        => $data['note'] ?? null,
            'status'      => 'pending',
        ]);

        $family = Family::find($familyMember->family_id);
        if ($family && $family->father_id) {
            User::find($family->father_id)?->notify(new FundRequestSubmitted($fund->load('user')));
        }

        return redirect('/admin/fund-request/my')->with('success', 'Fund request submitted.');
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.fund_request.approve']);

        $fund = FundRequest::findOrFail($id);

        $data = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:1'],
        ]);

        if (!$this->isSuperadmin()) {
            $family = Family::where('father_id', Auth::id())->first();
            if (!$family || $fund->family_id !== $family->id) {
                return back()->with('error', 'Not authorized.');
            }
        }

        $approvedAmount = $data['amount'] ?? $fund->amount;

        $mainBudget = Budget::where('family_id', $fund->family_id)
            ->whereNull('user_id')
            ->where('month', now()->format('Y-m'))
            ->first();

        if ($mainBudget && $mainBudget->amount < $approvedAmount) {
            return back()->with('error', 'Insufficient family budget for this month.');
        }

        DB::transaction(function () use ($fund, $approvedAmount, $mainBudget) {
            if ($mainBudget) {
                $mainBudget->amount -= $approvedAmount;
                $mainBudget->save();

                BudgetTransaction::create([
                    'budget_id' => $mainBudget->id,
                    'user_id'   => Auth::id(),
                    'action'    => 'deduct',
                    'amount'    => $approvedAmount,
                    'source'    => 'fund_request',
                    'source_id' => $fund->id,
                ]);
            }

            $fund->status = 'approved';
            $fund->amount = $approvedAmount;
            $fund->save();

            $assignedBudget = Budget::create([
                'family_id'      => $fund->family_id,
                'user_id'        => $fund->user_id,
                'category_id'    => $fund->category_id,
                'amount'         => $approvedAmount,
                'initial_amount' => $approvedAmount,
                'type'           => 'assigned',
                'month'          => now()->format('Y-m'),
            ]);

            Expense::create([
                'user_id'     => $fund->user_id,
                'budget_id'   => $assignedBudget->id,
                'category_id' => $fund->category_id,
                'title'       => 'Fund Approved',
                'amount'      => $approvedAmount,
                'note'        => $fund->note,
                'date'        => Carbon::now(),
                'approved'    => true,
            ]);
        });

        User::find($fund->user_id)?->notify(new FundRequestApproved($fund));

        return back()->with('success', "Fund request approved for {$approvedAmount}.");
    }

    public function decline(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.fund_request.approve']);

        $fund = FundRequest::findOrFail($id);

        if (!$this->isSuperadmin()) {
            $family = Family::where('father_id', Auth::id())->first();
            if (!$family || $fund->family_id !== $family->id) {
                return back()->with('error', 'Not authorized.');
            }
        }

        $fund->status = 'rejected';
        $fund->save();

        User::find($fund->user_id)?->notify(new FundRequestDeclined($fund));

        return back()->with('success', 'Fund request declined.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $fund = FundRequest::findOrFail($id);

        if ($fund->user_id !== Auth::id() && !$this->isSuperadmin()) {
            return back()->with('error', 'You can only delete your own requests.');
        }

        if ($fund->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be deleted.');
        }

        $fund->delete();

        return back()->with('success', 'Fund request deleted.');
    }
}
