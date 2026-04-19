<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->call([
            UserSeeder::class,
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            FamilySeeder::class,
            CategorySeeder::class,
            BudgetSeeder::class,
            ExpenseSeeder::class,
            IncomeSeeder::class,
            FundRequestSeeder::class,
            LoanCategorySeeder::class,
            LoanSeeder::class,
            GoalSeeder::class,
            SavingsSeeder::class,
            PostSeeder::class,
            FollowSeeder::class,
            ActionLogSeeder::class,
            FamilyMemberInvitationSeeder::class,
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
