<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllPostsCollection;
use App\Models\Post;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $authUserId = auth()->id();

            $posts = Post::whereHas('user', function ($query) use ($authUserId) {
                $query->where('is_private', false)
                      ->orWhere(function ($query) use ($authUserId) {
                          $query->where('is_private', true)
                                ->whereHas('followers', function ($query) use ($authUserId) {
                                    $query->where('follower_id', $authUserId);
                                });
                      })
                      ->orWhere('id', $authUserId);
            })->orderBy('created_at', 'desc')->get();

            return response()->json(new AllPostsCollection($posts), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
