<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Events\CommentUpdated;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required',
            'comment' => 'required',
        ]);

        try {
            $comment = new Comment;

            $comment->post_id = $request->input('post_id');
            $comment->user_id = auth()->user()->id;
            $comment->text = $request->input('comment');

            $comment->save();

            broadcast(new CommentUpdated($comment->post_id, $comment))->toOthers();

            return response()->json(['comment' => $comment], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $comment = Comment::find($id);
            $postId = $comment->post_id;
            $comment->delete();

            broadcast(new CommentUpdated($postId, null))->toOthers();

            return response()->json(['message' => 'Comment deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
