<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Income;
use Illuminate\Database\Seeder;

class IncomeSeeder extends Seeder
{
    public function run(): void
    {
        Income::truncate();

        // Salary amounts by family
        $familySalary = [
            'Khan Family'        => ['father' => 80000, 'mother' => 30000],
            'Ali Family'         => ['father' => 60000, 'mother' => 0],
            'Siddiqui Family'    => ['father' => 0,     'mother' => 55000], // single mother earns
            'Farhan Ali Family'  => ['father' => 120000, 'mother' => 45000],
            'Hassan Family'      => ['father' => 50000, 'mother' => 15000],
        ];

        $today = now();
        $months = [2, 1, 0]; // 2 months ago, 1 month ago, current

        foreach (Family::with('members')->get() as $family) {
            $salaries = $familySalary[$family->name] ?? ['father' => 50000, 'mother' => 0];

            foreach ($family->members as $member) {
                if (!in_array($member->role, ['father', 'mother'], true)) {
                    continue; // children don't earn salary in seed data
                }

                $salaryAmount = $salaries[$member->role] ?? 0;
                if ($salaryAmount <= 0) {
                    continue;
                }

                // Monthly salary for 3 months (only the first has recurring=true)
                foreach ($months as $i => $monthsAgo) {
                    $date = $today->copy()->subMonths($monthsAgo)->startOfMonth()->addDay();
                    Income::create([
                        'user_id'             => $member->user_id,
                        'family_id'           => $family->id,
                        'source'              => 'salary',
                        'title'               => 'Monthly Salary',
                        'amount'              => $salaryAmount,
                        'note'                => 'Regular monthly salary',
                        'received_on'         => $date->toDateString(),
                        'recurring'           => $i === count($months) - 1, // mark last (current) as recurring
                        'recurrence_interval' => $i === count($months) - 1 ? 'monthly' : null,
                    ]);
                }

                // One-off freelance / gift for variety
                if ($member->role === 'father' && $family->name === 'Farhan Ali Family') {
                    Income::create([
                        'user_id'     => $member->user_id,
                        'family_id'   => $family->id,
                        'source'      => 'freelance',
                        'title'       => 'Website Project',
                        'amount'      => 25000,
                        'note'        => 'Freelance delivery',
                        'received_on' => $today->copy()->subDays(10)->toDateString(),
                        'recurring'   => false,
                    ]);
                }
                if ($member->role === 'mother' && $family->name === 'Khan Family') {
                    Income::create([
                        'user_id'     => $member->user_id,
                        'family_id'   => $family->id,
                        'source'      => 'gift',
                        'title'       => 'Eid Gift',
                        'amount'      => 5000,
                        'note'        => 'Received from family',
                        'received_on' => $today->copy()->subDays(25)->toDateString(),
                        'recurring'   => false,
                    ]);
                }
            }
        }

        $this->command->info('Seeded ' . Income::count() . ' income entries (3 months of salary + one-offs).');
    }
}
