<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
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

        return $this->success('Reaction added', $reaction);
    }
}
