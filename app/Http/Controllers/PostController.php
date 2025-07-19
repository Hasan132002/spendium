<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    // public function index()
    // {
    //     $posts = Post::with(['user', 'comments', 'reactions'])->latest()->get();
    //     return $this->success('Posts retrieved successfully', $posts);
    // }
    public function index()
{
    $posts = Post::with(['user', 'comments.reactions', 'reactions'])
        ->withCount([
            'comments as comment_count',
            'reactions as like_count'
        ])
        ->latest()
        ->get()
        ->map(function ($post) {
            // Add reaction count to each comment
            $post->comments->map(function ($comment) {
                $comment->reaction_count = $comment->reactions->count();
                unset($comment->reactions); // remove raw data if not needed
                return $comment;
            });
            return $post;
        });

    return $this->success('Posts retrieved successfully', $posts);
}

    public function myPosts()
    {
        $user = Auth::user();
        $posts = $user->posts()->with(['comments', 'reactions'])->latest()->get();
        return $this->success('My posts retrieved successfully', $posts);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('uploads', 'public');
            $data['photo'] = asset('storage/' . $path); 
        }

        $post = Auth::user()->posts()->create($data);
        return $this->success('Post created successfully', $post, 201);
    }

    public function update(Request $request, Post $post)
    {
        // $this->authorize('update', $post);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'photo' => 'nullable|image|max:2048',
        ]);

     if ($request->hasFile('photo')) {
    $path = $request->file('photo')->store('uploads', 'public');
    $data['photo'] = url('storage/' . $path); // full URL
}


        $post->update($data);

        return $this->success('Post updated successfully', $post);
    }


    public function show(Post $post)
    {
        $post->load(['user', 'comments.user', 'reactions']);
        return $this->success('Post retrieved successfully', $post);
    }



    public function destroy(Post $post)
    {
        // $this->authorize('delete', $post);
        $post->delete();

        return $this->success('Post deleted successfully');
    }
}
