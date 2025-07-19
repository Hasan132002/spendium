<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        return $this->success('Comment added successfully', $comment, 201);
    }

    public function index(Post $post)
    {
        $comments = $post->comments()->with('user', 'reactions')->latest()->get();
        return $this->success('Comments retrieved successfully', $comments);
    }
}
