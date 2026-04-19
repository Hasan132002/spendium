<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\Support\Renderable;

class CommunityController extends Controller
{
    public function index(): Renderable
    {
        $posts = Post::with(['user:id,name,email', 'comments.user:id,name'])
            ->withCount(['comments', 'reactions'])
            ->latest()
            ->paginate(12);

        return view('community.index', compact('posts'));
    }

    public function show(int $postId): Renderable
    {
        $post = Post::with([
            'user:id,name,email',
            'comments' => fn ($q) => $q->latest()->with('user:id,name'),
        ])
        ->withCount(['comments', 'reactions'])
        ->findOrFail($postId);

        return view('community.show', compact('post'));
    }
}
