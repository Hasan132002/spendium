<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Carbon\Carbon;
use App\Models\Family;
use Illuminate\Support\Facades\Auth;
use App\Models\LoanCategory;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'loan_category_id' => 'required|exists:loan_categories,id',
            'lender' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'purpose' => 'nullable|string',
            'due_date' => 'required|date'
        ]);

        $family = Family::where('father_id', Auth::id())->firstOrFail();

        $loan = Loan::create([
            'family_id' => $family->id,
            'loan_category_id' => $request->loan_category_id,
            'lender' => $request->lender,
            'amount' => $request->amount,
            'remaining_amount' => $request->amount,
            'purpose' => $request->purpose,
            'due_date' => $request->due_date,
            'status' => 'pending'
        ]);

        return $this->success('Loan created successfully', $loan);
    }

    public function index()
    {
        $family = Family::where('father_id', Auth::id())->firstOrFail();

        $loans = Loan::with('category')
            ->where('family_id', $family->id)
            ->get();

        return $this->success('Loan list retrieved successfully', $loans);
    }

    public function show($id)
    {
        $loan = Loan::with(['repayments', 'contributions.user', 'category'])->findOrFail($id);

        return $this->success('Loan details retrieved successfully', $loan);
    }
}
