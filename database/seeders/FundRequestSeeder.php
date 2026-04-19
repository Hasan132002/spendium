<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Family;
use App\Models\FundRequest;
use Illuminate\Database\Seeder;

class FundRequestSeeder extends Seeder
{
    public function run(): void
    {
        FundRequest::truncate();

        $statuses = ['pending', 'approved', 'rejected'];
        $notes = ['Urgent', 'Monthly top-up', 'One-off expense', null];

        $defaultCategoryIds = Category::whereNull('user_id')->pluck('id')->all();
        if (empty($defaultCategoryIds)) {
            $this->command->warn('FundRequestSeeder: no default categories; run CategorySeeder first.');
            return;
        }

        foreach (Family::with('members')->get() as $family) {
            foreach ($family->members as $member) {
                if ($member->role === 'father') {
                    continue;
                }

                $count = rand(1, 3);
                for ($i = 0; $i < $count; $i++) {
                    FundRequest::create([
                        'user_id'     => $member->user_id,
                        'family_id'   => $family->id,
                        'category_id' => $defaultCategoryIds[array_rand($defaultCategoryIds)],
                        'amount'      => rand(1000, 5000),
                        'note'        => $notes[array_rand($notes)],
                        'status'      => $statuses[array_rand($statuses)],
                    ]);
                }
            }
        }

        $this->command->info('Seeded ' . FundRequest::count() . ' fund requests.');
    }
}
