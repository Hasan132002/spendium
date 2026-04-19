<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Family;
use App\Models\LoanCategory;
use Illuminate\Database\Seeder;

class LoanCategorySeeder extends Seeder
{
    public function run(): void
    {
        LoanCategory::truncate();

        $global = ['Home', 'Vehicle', 'Business', 'Education', 'Medical', 'Personal'];
        foreach ($global as $name) {
            LoanCategory::create([
                'name'      => $name,
                'user_id'   => null,
                'family_id' => null,
            ]);
        }

        foreach (Family::all() as $family) {
            LoanCategory::create([
                'name'      => 'Family Internal',
                'user_id'   => $family->father_id,
                'family_id' => $family->id,
            ]);
        }

        $this->command->info('Seeded ' . LoanCategory::count() . ' loan categories.');
    }
}
