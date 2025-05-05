<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllPostsCollection;
use App\Http\Resources\UsersCollection;
use App\Models\Post;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::where('id', $id)->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $authUser = Auth::user();
            $isFollowing = false;
            if ($authUser) {
                $isFollowing = $authUser->following()->where('following_id', $user->id)->exists();
            }

            $followersCount = $user->followers()->count();
            $followingCount = $user->following()->count();

            if ($user->is_private && (!$authUser || ($authUser->id !== $user->id && !$isFollowing))) {
                $posts = collect();
                $accountPrivate = true;
            } else {
                $posts = Post::where('user_id', $id)->orderBy('created_at', 'desc')->get();
                $accountPrivate = false;
            }

            return response()->json([
                'posts' => new AllPostsCollection($posts),
                'user' => new UsersCollection(collect([$user])),
                'followersCount' => $followersCount,
                'followingCount' => $followingCount,
                'isFollowing' => $isFollowing,
                'isPrivate' => $user->is_private,
                'accountPrivate' => $accountPrivate,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function togglePrivate(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $authUser->is_private = !$authUser->is_private;
        $authUser->save();

        return response()->json(['isPrivate' => $authUser->is_private], 200);
    }

    public function follow($id)
    {
        $authUser = Auth::user();
        if ($authUser->id == $id) {
            return response()->json(['error' => 'You cannot follow yourself'], 400);
        }

        $userToFollow = User::find($id);
        if (!$userToFollow) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $authUser->following()->syncWithoutDetaching([$id]);

        // Create notification for the user being followed
        \App\Models\Notification::create([
            'user_id' => $id,
            'type' => 'follow',
            'data' => json_encode([
                'follower_id' => $authUser->id,
                'follower_name' => $authUser->name,
                'message' => "{$authUser->name} started following you."
            ]),
        ]);

        return response()->json(['message' => 'Followed successfully']);
    }

    public function unfollow($id)
    {
        $authUser = Auth::user();
        if ($authUser->id == $id) {
            return response()->json(['error' => 'You cannot unfollow yourself'], 400);
        }

        $userToUnfollow = User::find($id);
        if (!$userToUnfollow) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $authUser->following()->detach($id);

        return response()->json(['message' => 'Unfollowed successfully']);
    }

    /**
     * Get liked videos/posts for the specified user.
     */
    public function liked($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Get posts liked by the user, ordered by like created_at descending
            $likedPosts = Post::whereHas('likes', function ($query) use ($id) {
                $query->where('user_id', $id);
            })->orderBy('created_at', 'desc')->get();

            return response()->json([
                'likedPosts' => new AllPostsCollection($likedPosts),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
