<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Family;
use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    public function run(): void
    {
        GoalContribution::truncate();
        Goal::truncate();

        // Realistic per-family family goal scenarios
        $familyGoalScenarios = [
            'Khan Family'        => ['title' => 'Hajj 2028',        'target' => 800000, 'saved' => 250000, 'deadline_months' => 18],
            'Ali Family'         => ['title' => 'Family Vacation',  'target' => 150000, 'saved' => 40000,  'deadline_months' => 6],
            'Siddiqui Family'    => ['title' => 'Kids Education',   'target' => 500000, 'saved' => 90000,  'deadline_months' => 24],
            'Farhan Ali Family'  => ['title' => 'House Renovation', 'target' => 1200000, 'saved' => 350000, 'deadline_months' => 12],
            'Hassan Family'      => ['title' => 'New Car',          'target' => 600000, 'saved' => 80000,  'deadline_months' => 15],
        ];

        $personalGoalTitles = ['Laptop', 'Bike', 'Marriage Fund', 'Course Fee', 'Emergency Fund', 'Mobile Upgrade', 'Gaming Console'];

        foreach (Family::with('members')->get() as $family) {
            $scenario = $familyGoalScenarios[$family->name] ?? [
                'title' => 'Family Savings', 'target' => 400000, 'saved' => 50000, 'deadline_months' => 12,
            ];

            // Family goal with specific deadline
            $familyGoal = Goal::create([
                'family_id'     => $family->id,
                'user_id'       => $family->father_id,
                'title'         => $scenario['title'],
                'target_amount' => $scenario['target'],
                'saved_amount'  => $scenario['saved'],
                'target_date'   => now()->addMonths($scenario['deadline_months'])->toDateString(),
                'type'          => 'family',
                'status'        => 'active',
            ]);

            // Members contribute to family goal
            foreach ($family->members as $member) {
                if ($member->role === 'child' && rand(0, 1)) {
                    continue; // not every child contributes
                }

                GoalContribution::create([
                    'goal_id' => $familyGoal->id,
                    'user_id' => $member->user_id,
                    'amount'  => rand(3000, 15000),
                    'note'    => 'Monthly contribution',
                ]);
            }

            // Personal goals per member (not fathers — they have the family goal)
            foreach ($family->members as $member) {
                if ($member->user_id === $family->father_id) {
                    continue;
                }

                $targetAmount = rand(30000, 150000);
                $savedAmount = (int) ($targetAmount * (rand(10, 60) / 100));

                $personalGoal = Goal::create([
                    'family_id'     => $family->id,
                    'user_id'       => $member->user_id,
                    'title'         => $personalGoalTitles[array_rand($personalGoalTitles)],
                    'target_amount' => $targetAmount,
                    'saved_amount'  => $savedAmount,
                    'target_date'   => now()->addMonths(rand(3, 12))->toDateString(),
                    'type'          => 'personal',
                    'status'        => 'active',
                ]);

                // 1-3 contributions per personal goal
                $count = rand(1, 3);
                for ($c = 0; $c < $count; $c++) {
                    GoalContribution::create([
                        'goal_id' => $personalGoal->id,
                        'user_id' => $member->user_id,
                        'amount'  => (int) ($savedAmount / max(1, $count)),
                        'note'    => 'Self contribution',
                    ]);
                }
            }
        }

        $this->command->info('Seeded ' . Goal::count() . ' goals with ' . GoalContribution::count() . ' contributions (target dates set).');
    }
}
