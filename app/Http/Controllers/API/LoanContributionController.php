<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\{LoanCategory, Loan, LoanRepayment, LoanContribution, Family};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoanContributionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        $contribution = LoanContribution::create([
            'loan_id' => $request->loan_id,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'note' => $request->note,
            'status' => 'pending'
        ]);

        return $this->success('Contribution offered', $contribution);
    }

    public function myContributions()
    {
        $user = Auth::user();
        $family = Family::where('father_id', $user->id)->first();

        if ($family) {
            $memberIds = $family->members()->pluck('user_id');

            $contributions = LoanContribution::with([
                'loan.category:id,name',
                'user:id,name'
            ])
            ->whereIn('user_id', $memberIds)
            ->orderBy('created_at', 'desc')
            ->get();
        } else {
            $contributions = LoanContribution::with([
                'loan.category:id,name'
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        }

        return $this->success('Contributions fetched', $contributions);
    }

    public function approve($id)
    {
        $contribution = LoanContribution::findOrFail($id);
        $loan = $contribution->loan;
        $family = Family::where('father_id', Auth::id())->first();

        if (!$family || $loan->family_id !== $family->id) {
            return $this->error('Unauthorized', null, 403);
        }

        $loan->remaining_amount -= $contribution->amount;

        if ($loan->remaining_amount <= 0) {
            $loan->remaining_amount = 0;
            $loan->status = 'paid';
        } elseif ($loan->remaining_amount < $loan->amount) {
            $loan->status = 'partially_paid';
        }

        $loan->save();

        $contribution->status = 'approved';
        $contribution->save();

        LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => $contribution->amount,
            'date' => Carbon::now(),
            'note' => 'Contribution by ' . $contribution->user->name
        ]);

        return $this->success('Contribution approved and applied to loan');
    }

    public function decline($id)
    {
        $contribution = LoanContribution::findOrFail($id);
        $loan = $contribution->loan;
        $family = Family::where('father_id', Auth::id())->first();

        if (!$family || $loan->family_id !== $family->id) {
            return $this->error('Unauthorized', null, 403);
        }

        $contribution->status = 'declined';
        $contribution->save();

        return $this->success('Contribution declined');
    }
}
