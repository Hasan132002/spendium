<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\BudgetTransaction;
use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        Expense::truncate();

        $titles = [
            'Weekly Groceries', 'Electricity Bill', 'School Fee', 'Medicines',
            'Fuel Top-up', 'Internet Bill', 'Mobile Recharge', 'Restaurant Dinner',
            'Uber Ride', 'Stationery', 'Gas Bill', 'Pharmacy', 'Vegetables',
            'Meat Shop', 'Eid Clothes', 'Birthday Cake', 'Movie Tickets',
        ];

        $memberBudgets = Budget::whereNotNull('user_id')->get();

        foreach ($memberBudgets as $budget) {
            // Scenario: utilise ~60-95% of budget (some near threshold)
            $utilizationTarget = rand(60, 95) / 100;
            $remaining = (float) $budget->initial_amount * $utilizationTarget;
            $expenseCount = rand(3, 6);

            // Parse budget month to generate expense dates within that month
            [$year, $month] = explode('-', (string) $budget->month);
            $firstDay = mktime(0, 0, 0, (int) $month, 1, (int) $year);
            $lastDay = (int) date('t', $firstDay);

            for ($i = 0; $i < $expenseCount && $remaining > 0; $i++) {
                $maxPerExpense = $remaining / max(1, $expenseCount - $i);
                $amount = (int) min($remaining, max(200, rand(200, (int) max(500, $maxPerExpense))));
                $remaining -= $amount;

                $dayOfMonth = rand(1, min($lastDay, (int) date('t')));
                $expenseDate = sprintf('%04d-%02d-%02d', $year, $month, $dayOfMonth);

                $expense = Expense::create([
                    'user_id'     => $budget->user_id,
                    'budget_id'   => $budget->id,
                    'category_id' => $budget->category_id,
                    'title'       => $titles[array_rand($titles)],
                    'amount'      => $amount,
                    'note'        => 'Auto-seeded expense',
                    'date'        => $expenseDate,
                    'approved'    => rand(0, 10) > 2, // ~80% approved
                ]);

                BudgetTransaction::create([
                    'budget_id' => $budget->id,
                    'user_id'   => $budget->user_id,
                    'action'    => 'deduct',
                    'amount'    => $amount,
                    'source'    => 'expense',
                    'source_id' => $expense->id,
                ]);

                // Decrement running balance column on budget
                $budget->amount = max(0, (float) $budget->amount - $amount);
            }
            $budget->save();
        }

        $this->command->info('Seeded ' . Expense::count() . ' expenses across 3 months with realistic utilization.');
    }
}
