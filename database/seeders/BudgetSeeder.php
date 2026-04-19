<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\BudgetTransaction;
use App\Models\Category;
use App\Models\Family;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        BudgetTransaction::truncate();
        Budget::truncate();

        $months = [
            now()->subMonths(2)->format('Y-m'),
            now()->subMonth()->format('Y-m'),
            now()->format('Y-m'),
        ];

        // Per-family pool sizes to create variety
        $familyPools = [
            'Khan Family'        => 60000,
            'Ali Family'         => 45000,
            'Siddiqui Family'    => 30000, // single-mother, tighter budget
            'Farhan Ali Family'  => 80000, // larger family
            'Hassan Family'      => 40000,
        ];

        foreach (Family::with('members')->get() as $family) {
            $defaultCategoryIds = Category::whereNull('user_id')->pluck('id')->all();
            if (empty($defaultCategoryIds)) {
                $this->command->warn('BudgetSeeder: no default categories; run CategorySeeder first.');
                return;
            }

            $pool = $familyPools[$family->name] ?? 50000;

            foreach ($months as $month) {
                $mainBudget = Budget::create([
                    'family_id'      => $family->id,
                    'user_id'        => null,
                    'category_id'    => null,
                    'amount'         => $pool,
                    'initial_amount' => $pool,
                    'type'           => 'family',
                    'month'          => $month,
                ]);

                BudgetTransaction::create([
                    'budget_id' => $mainBudget->id,
                    'user_id'   => $family->father_id,
                    'action'    => 'add',
                    'amount'    => $pool,
                    'source'    => 'top_up',
                ]);

                foreach ($family->members as $member) {
                    if ($member->user_id === $family->father_id) {
                        continue;
                    }

                    $assigned = $member->role === 'mother'
                        ? (int) ($pool * 0.25)
                        : (int) ($pool * 0.10);

                    $categoryId = $defaultCategoryIds[array_rand($defaultCategoryIds)];

                    $memberBudget = Budget::create([
                        'family_id'      => $family->id,
                        'user_id'        => $member->user_id,
                        'category_id'    => $categoryId,
                        'amount'         => $assigned,
                        'initial_amount' => $assigned,
                        'type'           => 'assigned',
                        'month'          => $month,
                    ]);

                    BudgetTransaction::create([
                        'budget_id' => $mainBudget->id,
                        'user_id'   => $family->father_id,
                        'action'    => 'deduct',
                        'amount'    => $assigned,
                        'source'    => 'assign_to_member',
                        'source_id' => $memberBudget->id,
                    ]);

                    BudgetTransaction::create([
                        'budget_id' => $memberBudget->id,
                        'user_id'   => $family->father_id,
                        'action'    => 'add',
                        'amount'    => $assigned,
                        'source'    => 'assigned',
                    ]);
                }
            }
        }

        $this->command->info('Seeded ' . Budget::count() . ' budgets (3 months) with ' . BudgetTransaction::count() . ' transactions.');
    }
}
