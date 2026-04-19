<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Family;
use App\Models\Saving;
use App\Models\SavingsTransaction;
use Illuminate\Database\Seeder;

class SavingsSeeder extends Seeder
{
    public function run(): void
    {
        SavingsTransaction::truncate();
        Saving::truncate();

        $types = ['add', 'deduct', 'transfer_to_goal'];

        foreach (Family::with('members')->get() as $family) {
            foreach ($family->members as $member) {
                $total = 0;
                $saving = Saving::create([
                    'user_id' => $member->user_id,
                    'total'   => 0,
                ]);

                $txCount = rand(3, 6);
                for ($i = 0; $i < $txCount; $i++) {
                    $type = $types[array_rand($types)];
                    $amount = rand(500, 5000);

                    if ($type === 'add') {
                        $total += $amount;
                    } else {
                        if ($total < $amount) {
                            $type = 'add';
                            $total += $amount;
                        } else {
                            $total -= $amount;
                        }
                    }

                    SavingsTransaction::create([
                        'saving_id' => $saving->id,
                        'user_id'   => $member->user_id,
                        'type'      => $type,
                        'amount'    => $amount,
                        'note'      => ucfirst(str_replace('_', ' ', $type)),
                    ]);
                }

                $saving->update(['total' => $total]);
            }
        }

        $this->command->info('Seeded ' . Saving::count() . ' saving accounts with ' . SavingsTransaction::count() . ' transactions.');
    }
}
