<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{FundRequest, Family, Budget, Category, Expense, BudgetTransaction};
use Carbon\Carbon;

class FundRequestController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric',
            'note' => 'nullable|string'
        ]);

        $familyMember = auth()->user()->familyMember;

        if (!$familyMember || $familyMember->role === 'father') {
            return $this->error('Only mother/child can request funds', null, 403);
        }

        $fund = FundRequest::create([
            'user_id'     => auth()->id(),
            'family_id'   => $familyMember->family_id,
            'category_id' => $request->category_id,
            'amount'      => $request->amount,
            'note'        => $request->note,
            'status'      => 'pending',
        ]);

        return $this->success('Request submitted', $fund);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:1'
        ]);

        $fund = FundRequest::findOrFail($id);
        $family = Family::where('father_id', auth()->id())->first();

        if (!$family || $fund->family_id !== $family->id) {
            return $this->error('Not authorized', null, 403);
        }

        $approvedAmount = $request->amount ?? $fund->amount;

        $mainBudget = Budget::where('family_id', $family->id)
                            ->whereNull('user_id')
                            ->where('category_id', $fund->category_id)
                            ->first();

        if (!$mainBudget || $mainBudget->amount < $approvedAmount) {
            return $this->error('Insufficient family budget', null, 400);
        }

        $mainBudget->amount -= $approvedAmount;
        $mainBudget->save();

        $fund->status = 'approved';
        $fund->amount = $approvedAmount;
        $fund->save();

        $assignedBudget = Budget::create([
            'family_id'   => $fund->family_id,
            'user_id'     => $fund->user_id,
            'category_id' => $fund->category_id,
            'amount'      => $approvedAmount
        ]);

        BudgetTransaction::create([
            'budget_id'  => $mainBudget->id,
            'user_id'    => auth()->id(),
            'action'     => 'deduct',
            'amount'     => $approvedAmount,
            'source'     => 'fund_request',
            'source_id'  => $fund->id,
        ]);

        Expense::create([
            'user_id'     => $fund->user_id,
            'budget_id'   => $assignedBudget->id,
            'category_id' => $fund->category_id,
            'title'       => 'Fund Approved',
            'amount'      => $approvedAmount,
            'note'        => $fund->note,
            'date'        => Carbon::now(),
            'approved'    => true
        ]);

        return $this->success('Fund approved & assigned with expense recorded');
    }

    public function decline($id)
    {
        $fund = FundRequest::findOrFail($id);
        $family = Family::where('father_id', auth()->id())->first();

        if (!$family || $fund->family_id !== $family->id) {
            return $this->error('Not authorized', null, 403);
        }

        $fund->status = 'rejected';
        $fund->save();

        return $this->success('Fund request declined');
    }

    public function myRequests()
    {
        $requests = FundRequest::where('user_id', auth()->id())
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success('My requests retrieved', $requests);
    }

    public function allFamilyRequests()
    {
        $family = Family::where('father_id', auth()->id())->first();

        if (!$family) {
            return $this->error('Family not found', null, 400);
        }

        $requests = FundRequest::where('family_id', $family->id)
            ->with(['category:id,name', 'user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success('Family requests retrieved', $requests);
    }
}
