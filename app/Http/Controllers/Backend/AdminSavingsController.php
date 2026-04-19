<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Saving;
use App\Models\SavingsTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminSavingsController extends Controller
{
    public function addToSavings(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.savings.manage']);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'note'   => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            $saving = Saving::firstOrCreate(['user_id' => Auth::id()], ['total' => 0]);
            $saving->total += $data['amount'];
            $saving->save();

            SavingsTransaction::create([
                'saving_id' => $saving->id,
                'user_id'   => Auth::id(),
                'type'      => 'add',
                'amount'    => $data['amount'],
                'note'      => $data['note'] ?? 'Manual deposit',
            ]);
        });

        return back()->with('success', 'Amount added to savings.');
    }

    public function runEomRollover(): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.savings.manage']);

        $user = Auth::user();
        $month = now()->format('Y-m');

        $budgets = \App\Models\Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->get();

        $totalRemaining = 0;
        foreach ($budgets as $budget) {
            $remaining = (float) $budget->amount;
            if ($remaining > 0) {
                $totalRemaining += $remaining;
            }
        }

        if ($totalRemaining <= 0) {
            return back()->with('info', 'No remaining budget to roll over.');
        }

        DB::transaction(function () use ($user, $totalRemaining) {
            $saving = Saving::firstOrCreate(['user_id' => $user->id], ['total' => 0]);
            $saving->total += $totalRemaining;
            $saving->save();

            SavingsTransaction::create([
                'saving_id' => $saving->id,
                'user_id'   => $user->id,
                'type'      => 'add',
                'amount'    => $totalRemaining,
                'note'      => 'End-of-month rollover',
            ]);
        });

        return back()->with('success', 'Rolled over ' . config('app.currency_symbol', '$') . number_format($totalRemaining, 2) . ' into savings.');
    }
}
