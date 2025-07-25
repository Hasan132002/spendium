<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoanRepaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'loan_id' => 'required|exists:loans,id',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        $loan = Loan::findOrFail($request->loan_id);

        $loan->remaining_amount -= $request->amount;

        if ($loan->remaining_amount <= 0) {
            $loan->remaining_amount = 0;
            $loan->status = 'paid';
        } elseif ($loan->remaining_amount < $loan->amount) {
            $loan->status = 'partially_paid';
        }

        $loan->save();

        $repayment = LoanRepayment::create([
            'loan_id' => $loan->id,
            'amount' => $request->amount,
            'date' => Carbon::now(),
            'note' => $request->note
        ]);

        return $this->success('Repayment recorded successfully.', $repayment);
    }

    public function byLoan($loan_id)
    {
        $repayments = LoanRepayment::with('loan.category')
            ->where('loan_id', $loan_id)
            ->orderBy('date', 'desc')
            ->get();

        return $this->success('Repayment list fetched successfully.', $repayments);
    }
}
