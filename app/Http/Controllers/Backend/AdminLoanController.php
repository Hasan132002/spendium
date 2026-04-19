<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Loan;
use App\Models\LoanCategory;
use App\Models\LoanContribution;
use App\Models\LoanRepayment;
use App\Models\User;
use App\Notifications\LoanContributionApproved;
use App\Notifications\LoanContributionSubmitted;
use App\Traits\HasFamilyScope;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminLoanController extends Controller
{
    use HasFamilyScope;

    public function createLoan(): Renderable|RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.loan.manage']);

        $families = $this->familiesInScope();
        if ($families->isEmpty()) {
            return redirect()->route('admin.dashboard')->with('error', 'No family found.');
        }

        $categories = LoanCategory::all();

        return view('dashboard.loans.create', compact('families', 'categories'));
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.loan.manage']);

        $data = $request->validate([
            'family_id'        => ['required', 'integer', 'exists:families,id'],
            'loan_category_id' => ['required', 'integer', 'exists:loan_categories,id'],
            'lender'           => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'purpose'          => ['nullable', 'string', 'max:1000'],
            'due_date'         => ['nullable', 'date'],
        ]);

        Loan::create([
            'family_id'        => $data['family_id'],
            'loan_category_id' => $data['loan_category_id'],
            'lender'           => $data['lender'],
            'amount'           => $data['amount'],
            'remaining_amount' => $data['amount'],
            'purpose'          => $data['purpose'] ?? null,
            'status'           => 'pending',
            'due_date'         => $data['due_date'] ?? null,
        ]);

        return redirect()->route('admin.loans.index')->with('success', 'Loan created.');
    }

    public function addRepayment(Request $request, int $loanId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.loan.manage']);

        $loan = Loan::findOrFail($loanId);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date'   => ['required', 'date'],
            'note'   => ['nullable', 'string'],
        ]);

        if ((float) $data['amount'] > (float) $loan->remaining_amount) {
            return back()->with('error', 'Repayment amount exceeds remaining loan balance.');
        }

        DB::transaction(function () use ($loan, $data) {
            LoanRepayment::create([
                'loan_id' => $loan->id,
                'amount'  => $data['amount'],
                'date'    => $data['date'],
                'note'    => $data['note'] ?? null,
            ]);

            $loan->remaining_amount -= $data['amount'];
            if ($loan->remaining_amount <= 0) {
                $loan->remaining_amount = 0;
                $loan->status = 'paid';
            } else {
                $loan->status = 'partially_paid';
            }
            $loan->save();
        });

        return back()->with('success', 'Repayment recorded.');
    }

    public function destroyLoan(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.loan.manage']);

        $loan = Loan::findOrFail($id);
        $loan->delete();

        return redirect()->route('admin.loans.index')->with('success', 'Loan deleted.');
    }

    /** Member contributes to a loan. */
    public function contribute(Request $request, int $loanId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.loan.contribute']);

        $loan = Loan::findOrFail($loanId);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'note'   => ['nullable', 'string'],
        ]);

        $contribution = LoanContribution::create([
            'loan_id' => $loan->id,
            'user_id' => Auth::id(),
            'amount'  => $data['amount'],
            'note'    => $data['note'] ?? null,
            'status'  => 'pending',
        ]);

        $family = Family::find($loan->family_id);
        if ($family && $family->father_id !== Auth::id()) {
            User::find($family->father_id)?->notify(new LoanContributionSubmitted($contribution->load('user')));
        }

        return back()->with('success', 'Contribution submitted for approval.');
    }

    public function approveContribution(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.loan.manage']);

        $contribution = LoanContribution::findOrFail($id);
        $loan = $contribution->loan;

        if (!$this->isSuperadmin()) {
            $family = Family::where('father_id', Auth::id())->first();
            if (!$family || $loan->family_id !== $family->id) {
                return back()->with('error', 'Not authorized.');
            }
        }

        DB::transaction(function () use ($contribution, $loan) {
            $loan->remaining_amount -= $contribution->amount;
            if ($loan->remaining_amount <= 0) {
                $loan->remaining_amount = 0;
                $loan->status = 'paid';
            } else {
                $loan->status = 'partially_paid';
            }
            $loan->save();

            $contribution->status = 'approved';
            $contribution->save();

            LoanRepayment::create([
                'loan_id' => $loan->id,
                'amount'  => $contribution->amount,
                'date'    => Carbon::now(),
                'note'    => 'Contribution by ' . ($contribution->user->name ?? 'member'),
            ]);
        });

        User::find($contribution->user_id)?->notify(new LoanContributionApproved($contribution));

        return back()->with('success', 'Contribution approved and applied to loan.');
    }

    public function declineContribution(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['family.loan.manage']);

        $contribution = LoanContribution::findOrFail($id);

        if (!$this->isSuperadmin()) {
            $family = Family::where('father_id', Auth::id())->first();
            if (!$family || $contribution->loan->family_id !== $family->id) {
                return back()->with('error', 'Not authorized.');
            }
        }

        $contribution->status = 'rejected';
        $contribution->save();

        return back()->with('success', 'Contribution declined.');
    }

    public function storeLoanCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $familyMember = Auth::user()->familyMember;

        LoanCategory::create([
            'name'      => $data['name'],
            'user_id'   => Auth::id(),
            'family_id' => $familyMember?->family_id,
        ]);

        return back()->with('success', 'Loan category created.');
    }
}
