<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Family;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::truncate();

        $defaults = ['Groceries', 'Utilities', 'Rent', 'Transport', 'Education', 'Medical', 'Entertainment', 'Savings'];
        foreach ($defaults as $name) {
            Category::create(['name' => $name, 'user_id' => null, 'family_id' => null]);
        }

        foreach (Family::with('members')->get() as $family) {
            $custom = ['Eid Shopping', 'Birthday Fund', 'Emergency'];
            foreach ($custom as $name) {
                Category::create([
                    'name'      => $name,
                    'user_id'   => $family->father_id,
                    'family_id' => $family->id,
                ]);
            }
        }

        $this->command->info('Seeded ' . Category::count() . ' categories.');
    }
}
