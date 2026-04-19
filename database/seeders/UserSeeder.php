<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Explicit named users for all families + superadmin.
     * Password for all: 12345678
     */
    public function run(): void
    {
        $password = Hash::make('12345678');

        $users = [
            // Superadmin
            ['name' => 'Super Admin', 'email' => 'superadmin@spendium.com', 'username' => 'superadmin', 'role' => null],

            // Family 1: Khan Family (canonical test family)
            ['name' => 'Father Khan',  'email' => 'father@spendium.com',  'username' => 'father_khan',  'role' => 'father'],
            ['name' => 'Mother Khan',  'email' => 'mother@spendium.com',  'username' => 'mother_khan',  'role' => 'mother'],
            ['name' => 'Ahmed Khan',   'email' => 'child1@spendium.com',  'username' => 'child1_khan',  'role' => 'child'],
            ['name' => 'Sara Khan',    'email' => 'child2@spendium.com',  'username' => 'child2_khan',  'role' => 'child'],

            // Family 2: Ali Family
            ['name' => 'Ali Ahmed',    'email' => 'ali@spendium.com',     'username' => 'ali_ahmed',    'role' => 'father'],
            ['name' => 'Fatima Ali',   'email' => 'fatima@spendium.com',  'username' => 'fatima_ali',   'role' => 'mother'],
            ['name' => 'Hasan Ali',    'email' => 'hasan@spendium.com',   'username' => 'hasan_ali',    'role' => 'child'],

            // Family 3: Zara Family (single-mother household)
            ['name' => 'Zara Siddiqui', 'email' => 'zara@spendium.com',   'username' => 'zara_sidd',    'role' => 'mother'],
            ['name' => 'Ayesha Siddiqui', 'email' => 'ayesha@spendium.com', 'username' => 'ayesha_sidd', 'role' => 'child'],
            ['name' => 'Omar Siddiqui', 'email' => 'omar@spendium.com',   'username' => 'omar_sidd',    'role' => 'child'],

            // Family 4: Farhan Ali Family (large)
            ['name' => 'Farhan Ali',   'email' => 'farhanali@spendium.com', 'username' => 'farhan_ali',  'role' => 'father'],
            ['name' => 'Nadia Farhan', 'email' => 'nadia@spendium.com',   'username' => 'nadia_farhan', 'role' => 'mother'],
            ['name' => 'Zain Farhan',  'email' => 'zain@spendium.com',    'username' => 'zain_farhan',  'role' => 'child'],
            ['name' => 'Maryam Farhan', 'email' => 'maryam@spendium.com', 'username' => 'maryam_farhan', 'role' => 'child'],

            // Family 5: Bilal Family (smaller, newer)
            ['name' => 'Bilal Hassan', 'email' => 'bilal@spendium.com',   'username' => 'bilal_hassan', 'role' => 'father'],
            ['name' => 'Sana Bilal',   'email' => 'sana@spendium.com',    'username' => 'sana_bilal',   'role' => 'mother'],
            ['name' => 'Adeel Hassan', 'email' => 'adeel@spendium.com',   'username' => 'adeel_hassan', 'role' => 'child'],
        ];

        $rows = [];
        $now = now();
        foreach ($users as $u) {
            $rows[] = [
                'name'       => $u['name'],
                'email'      => $u['email'],
                'username'   => $u['username'],
                'password'   => $password,
                'role'       => $u['role'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        User::insert($rows);

        // A handful of factory users so social feed / posts / follows have variety
        User::factory()->count(20)->create();

        $this->command->info('Users seeded: ' . User::count() . ' total (17 named + ~20 factory).');
    }
}
