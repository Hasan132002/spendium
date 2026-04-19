<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class FamilySeeder extends Seeder
{
    public function run(): void
    {
        // Refresh Spatie permission cache so newly-created permissions from
        // RolePermissionSeeder are visible when we call syncPermissions below.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        FamilyMember::truncate();
        Family::truncate();

        $families = [
            [
                'name'  => 'Khan Family',
                'head'  => 'father@spendium.com',
                'members' => [
                    ['email' => 'father@spendium.com', 'role' => 'father'],
                    ['email' => 'mother@spendium.com', 'role' => 'mother'],
                    ['email' => 'child1@spendium.com', 'role' => 'child'],
                    ['email' => 'child2@spendium.com', 'role' => 'child'],
                ],
            ],
            [
                'name'  => 'Ali Family',
                'head'  => 'ali@spendium.com',
                'members' => [
                    ['email' => 'ali@spendium.com',    'role' => 'father'],
                    ['email' => 'fatima@spendium.com', 'role' => 'mother'],
                    ['email' => 'hasan@spendium.com',  'role' => 'child'],
                ],
            ],
            [
                'name'  => 'Siddiqui Family',
                'head'  => 'zara@spendium.com',
                'members' => [
                    ['email' => 'zara@spendium.com',   'role' => 'mother'],
                    ['email' => 'ayesha@spendium.com', 'role' => 'child'],
                    ['email' => 'omar@spendium.com',   'role' => 'child'],
                ],
            ],
            [
                'name'  => 'Farhan Ali Family',
                'head'  => 'farhanali@spendium.com',
                'members' => [
                    ['email' => 'farhanali@spendium.com', 'role' => 'father'],
                    ['email' => 'nadia@spendium.com',     'role' => 'mother'],
                    ['email' => 'zain@spendium.com',      'role' => 'child'],
                    ['email' => 'maryam@spendium.com',    'role' => 'child'],
                ],
            ],
            [
                'name'  => 'Hassan Family',
                'head'  => 'bilal@spendium.com',
                'members' => [
                    ['email' => 'bilal@spendium.com', 'role' => 'father'],
                    ['email' => 'sana@spendium.com',  'role' => 'mother'],
                    ['email' => 'adeel@spendium.com', 'role' => 'child'],
                ],
            ],
        ];

        foreach ($families as $data) {
            $head = User::where('email', $data['head'])->first();
            if (!$head) {
                $this->command->warn("FamilySeeder: head user {$data['head']} not found.");
                continue;
            }

            $family = Family::create([
                'name'      => $data['name'],
                'father_id' => $head->id,
            ]);

            foreach ($data['members'] as $m) {
                $user = User::where('email', $m['email'])->first();
                if (!$user) {
                    continue;
                }

                FamilyMember::create([
                    'family_id' => $family->id,
                    'user_id'   => $user->id,
                    'role'      => $m['role'],
                    'status'    => 'accepted',
                ]);

                // Spatie role assignment based on family role
                if ($user->id === $head->id) {
                    if (!$user->hasRole('Superadmin') && !$user->hasRole('Family Head')) {
                        $user->assignRole('Family Head');
                    }
                } else {
                    if (!$user->hasRole('Family Member')) {
                        $user->assignRole('Family Member');
                    }
                    // Mother gets extra family.* view permissions for demo
                    if ($m['role'] === 'mother') {
                        $user->syncPermissions([
                            'family.expense.view',
                            'family.fund_request.view',
                            'family.goal.view',
                            'family.income.view',
                        ]);
                    }
                }
            }
        }

        $this->command->info('Seeded ' . Family::count() . ' families with ' . FamilyMember::count() . ' members.');
    }
}
