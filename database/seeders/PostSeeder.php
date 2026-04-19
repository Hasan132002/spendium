<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Reaction::truncate();
        Comment::truncate();
        Post::truncate();

        $titles = [
            'Monthly Budget Tips',
            'How I Saved 20% This Month',
            'Family Expense Tracking',
            'Loan Repayment Journey',
            'Goal Achieved!',
            'Smart Grocery Shopping',
            'Investment Basics',
            'Emergency Fund Matters',
        ];
        $descriptions = [
            'Sharing what worked for me this month.',
            'Small changes, big impact on monthly savings.',
            'Here is a simple approach that helped our family stay on track.',
            'Finally closed a long-running loan — here is how.',
            'Consistent small contributions beat big one-offs.',
        ];
        $reactionTypes = ['like', 'love', 'wow'];

        $users = User::orderBy('id')->limit(20)->get();

        if ($users->count() < 2) {
            $this->command->warn('PostSeeder: need at least 2 users.');
            return;
        }

        foreach ($users as $user) {
            $postCount = rand(1, 3);
            for ($i = 0; $i < $postCount; $i++) {
                $post = Post::create([
                    'user_id'     => $user->id,
                    'title'       => $titles[array_rand($titles)],
                    'description' => $descriptions[array_rand($descriptions)],
                    'photo'       => null,
                ]);

                $commenters = $users->random(min(3, $users->count()));
                foreach ($commenters as $commenter) {
                    Comment::create([
                        'user_id' => $commenter->id,
                        'post_id' => $post->id,
                        'content' => 'Great insight, thanks for sharing!',
                    ]);
                }

                $reactors = $users->random(min(5, $users->count()));
                foreach ($reactors as $reactor) {
                    Reaction::firstOrCreate(
                        [
                            'user_id'        => $reactor->id,
                            'reactable_id'   => $post->id,
                            'reactable_type' => Post::class,
                        ],
                        ['type' => $reactionTypes[array_rand($reactionTypes)]]
                    );
                }
            }
        }

        foreach (Comment::inRandomOrder()->limit(20)->get() as $comment) {
            $reactor = $users->random();
            Reaction::firstOrCreate(
                [
                    'user_id'        => $reactor->id,
                    'reactable_id'   => $comment->id,
                    'reactable_type' => Comment::class,
                ],
                ['type' => 'like']
            );
        }

        $this->command->info('Seeded ' . Post::count() . ' posts, ' . Comment::count() . ' comments, ' . Reaction::count() . ' reactions.');
    }
}
