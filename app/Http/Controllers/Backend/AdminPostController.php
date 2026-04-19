<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use App\Notifications\NewComment;
use App\Notifications\NewFollower;
use App\Notifications\NewReaction;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPostController extends Controller
{
    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['personal.post.manage']);
        return view('dashboard.posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.post.manage']);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'photo'       => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('posts', 'public');
            $data['photo'] = asset('storage/' . $path);
        }

        Post::create([
            'user_id'     => Auth::id(),
            'title'       => $data['title'],
            'description' => $data['description'],
            'photo'       => $data['photo'] ?? null,
        ]);

        return redirect('/admin/my-posts')->with('success', 'Post created.');
    }

    public function edit(int $id): Renderable
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);
        return view('dashboard.posts.edit', compact('post'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $post = Post::where('user_id', Auth::id())->findOrFail($id);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'photo'       => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('posts', 'public');
            $data['photo'] = asset('storage/' . $path);
        }

        $post->update($data);

        return redirect('/admin/my-posts')->with('success', 'Post updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $post = Post::findOrFail($id);
        if ($post->user_id !== Auth::id() && !auth()->user()->hasRole('Superadmin')) {
            return back()->with('error', 'You can only delete your own posts.');
        }
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }

    public function comment(Request $request, int $postId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.post.manage']);

        $data = $request->validate(['content' => ['required', 'string']]);

        $post = Post::findOrFail($postId);
        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        if ($post->user_id !== Auth::id()) {
            $post->user?->notify(new NewComment($comment->load('user')));
        }

        return back()->with('success', 'Comment added.');
    }

    public function react(int $postId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.post.manage']);

        $post = Post::findOrFail($postId);

        $existing = $post->reactions()->where('user_id', Auth::id())->first();

        if ($existing) {
            $existing->delete();
            return back()->with('info', 'Reaction removed.');
        }

        $reaction = $post->reactions()->create([
            'user_id' => Auth::id(),
            'type'    => 'like',
        ]);

        if ($post->user_id !== Auth::id()) {
            $post->user?->notify(new NewReaction($reaction->load('user')));
        }

        return back()->with('success', 'Reaction added.');
    }

    public function toggleFollow(int $userId): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['personal.profile.view']);

        $auth = Auth::user();
        $target = User::findOrFail($userId);

        if ($auth->id === $target->id) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        if ($auth->followings()->where('following_id', $target->id)->exists()) {
            $auth->followings()->detach($target->id);
            return back()->with('info', 'Unfollowed ' . $target->name . '.');
        }

        $auth->followings()->attach($target->id);
        $target->notify(new NewFollower($auth));

        return back()->with('success', 'Following ' . $target->name . '.');
    }
}
