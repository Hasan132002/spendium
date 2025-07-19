<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
   public function toggleFollow(User $user)
{
    $authUser = Auth::user();

    if ($authUser->id === $user->id) {
        return $this->error('You cannot follow yourself.');
    }

    $isFollowing = $authUser->followings()->where('following_id', $user->id)->exists();

    if ($isFollowing) {
        $authUser->followings()->detach($user->id);
        return $this->success('User unfollowed successfully.');
    } else {
        $authUser->followings()->attach($user->id);
        return $this->success('User followed successfully.');
    }
}

    public function followers(User $user)
    {
        $followers = $user->followers()->with('posts')->get();
        return $this->success('Followers fetched successfully.', $followers);
    }

    public function followings(User $user)
    {
        $followings = $user->followings()->with('posts')->get();
        return $this->success('Followings fetched successfully.', $followings);
    }

    public function profileStats(User $user)
{
    $followerCount = $user->followers()->count();      // assuming 'followers' relation exists
    $followingCount = $user->followings()->count();    // assuming 'followings' relation exists
    $uploadCount = $user->posts()->whereNotNull('photo')->count();
    $totalPosts = $user->posts()->count();

    $posts = $user->posts()
        ->with([
            'reactions', 
            'comments' => function ($query) {
                $query->withCount('reactions')->with('user');
            }
        ])
        ->withCount(['reactions as likes_count', 'comments as comments_count'])
        ->latest()
        ->get();

    return $this->success('User stats fetched', [
        'user' => $user,
        'total_posts' => $totalPosts,
        'upload_count' => $uploadCount,
        'followers' => $followerCount,
        'followings' => $followingCount,
        'posts' => $posts,
    ]);
}

}
