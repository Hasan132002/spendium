<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Notifications\NewReaction;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    public function togglePostReaction(Post $post)
    {
        return $this->toggleReaction($post);
    }

    public function toggleCommentReaction(Comment $comment)
    {
        return $this->toggleReaction($comment);
    }

    protected function toggleReaction($model)
    {
        $user = Auth::user();

        $existing = $model->reactions()->where('user_id', $user->id)->first();

        if ($existing) {
            $existing->delete();
            return $this->success('Reaction removed');
        }

        $reaction = $model->reactions()->create([
            'user_id' => $user->id,
            'type' => 'like',
        ]);

        if (isset($model->user_id) && $model->user_id !== $user->id) {
            $model->user?->notify(new NewReaction($reaction->load('user')));
        }

        return $this->success('Reaction added', $reaction);
    }
}
