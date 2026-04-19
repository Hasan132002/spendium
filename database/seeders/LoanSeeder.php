<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Family;
use App\Models\Loan;
use App\Models\LoanCategory;
use App\Models\LoanContribution;
use App\Models\LoanRepayment;
use Illuminate\Database\Seeder;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        LoanContribution::truncate();
        LoanRepayment::truncate();
        Loan::truncate();

        $statuses = ['pending', 'partially_paid', 'paid'];
        $lenders = ['HBL Bank', 'Meezan Bank', 'Uncle Ali', 'Cousin Zahid', 'Company Advance'];
        $contribStatuses = ['pending', 'approved', 'rejected'];

        foreach (Family::with('members')->get() as $family) {
            $globalCats = LoanCategory::whereNull('family_id')->pluck('id')->all();
            $familyCat = LoanCategory::where('family_id', $family->id)->first();

            $loansToSeed = 2;
            for ($i = 0; $i < $loansToSeed; $i++) {
                $amount = rand(20000, 100000);
                $status = $statuses[array_rand($statuses)];

                $remaining = match ($status) {
                    'paid'           => 0,
                    'partially_paid' => (int) ($amount * 0.4),
                    default          => $amount,
                };

                $loan = Loan::create([
                    'family_id'        => $family->id,
                    'loan_category_id' => $familyCat && rand(0, 1) ? $familyCat->id : $globalCats[array_rand($globalCats)],
                    'lender'           => $lenders[array_rand($lenders)],
                    'amount'           => $amount,
                    'purpose'          => 'Seeded loan #' . ($i + 1),
                    'remaining_amount' => $remaining,
                    'status'           => $status,
                    'due_date'         => now()->addMonths(rand(1, 12))->toDateString(),
                ]);

                $paidSoFar = $amount - $remaining;
                if ($paidSoFar > 0) {
                    $chunks = rand(1, 3);
                    $chunkAmt = (int) ($paidSoFar / $chunks);
                    for ($r = 0; $r < $chunks; $r++) {
                        LoanRepayment::create([
                            'loan_id' => $loan->id,
                            'amount'  => $chunkAmt,
                            'date'    => now()->subDays(rand(5, 90))->toDateString(),
                            'note'    => 'Installment ' . ($r + 1),
                        ]);
                    }
                }

                foreach ($family->members as $member) {
                    if (rand(0, 1) === 1) {
                        LoanContribution::create([
                            'loan_id' => $loan->id,
                            'user_id' => $member->user_id,
                            'amount'  => rand(1000, 5000),
                            'note'    => 'Helping with repayment',
                            'status'  => $contribStatuses[array_rand($contribStatuses)],
                        ]);
                    }
                }
            }
        }

        $this->command->info('Seeded ' . Loan::count() . ' loans, ' . LoanRepayment::count() . ' repayments, ' . LoanContribution::count() . ' contributions.');
    }
}
