<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ActionLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActionLogSeeder extends Seeder
{
    public function run(): void
    {
        ActionLog::truncate();

        $types = ['auth', 'budget', 'expense', 'loan', 'goal', 'user', 'settings'];
        $titles = [
            'auth'     => ['User logged in', 'User logged out', 'Password reset'],
            'budget'   => ['Budget created', 'Budget assigned', 'Budget updated'],
            'expense'  => ['Expense added', 'Expense approved', 'Expense deleted'],
            'loan'     => ['Loan created', 'Repayment recorded', 'Contribution added'],
            'goal'     => ['Goal created', 'Goal contribution added'],
            'user'     => ['User created', 'User role updated'],
            'settings' => ['Settings updated'],
        ];

        $users = User::orderBy('id')->limit(10)->get();

        if ($users->isEmpty()) {
            $this->command->warn('ActionLogSeeder: no users found.');
            return;
        }

        foreach ($users as $user) {
            $entries = rand(3, 6);
            for ($i = 0; $i < $entries; $i++) {
                $type = $types[array_rand($types)];
                ActionLog::create([
                    'action_by' => $user->id,
                    'type'      => $type,
                    'title'     => $titles[$type][array_rand($titles[$type])],
                    'data'      => json_encode([
                        'ip'         => '127.0.0.1',
                        'user_agent' => 'SeederBot/1.0',
                        'timestamp'  => now()->toIso8601String(),
                    ]),
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Seeded ' . ActionLog::count() . ' action log entries.');
    }
}
