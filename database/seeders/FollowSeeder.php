<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('follows')->truncate();

        $users = User::orderBy('id')->limit(30)->get();

        if ($users->count() < 2) {
            $this->command->warn('FollowSeeder: need at least 2 users.');
            return;
        }

        $pairs = [];
        foreach ($users as $user) {
            $targets = $users->where('id', '!=', $user->id)->random(min(5, $users->count() - 1));
            foreach ($targets as $target) {
                $pairs[] = [
                    'follower_id'  => $user->id,
                    'following_id' => $target->id,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
        }

        DB::table('follows')->insertOrIgnore($pairs);

        $this->command->info('Seeded ' . DB::table('follows')->count() . ' follow relationships.');
    }
}
