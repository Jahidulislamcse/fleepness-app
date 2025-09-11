<?php

namespace App\Http\Controllers\LiveStreaming;

use App\Models\LivestreamComment;
use App\Models\Livestream;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LivestreamCommentController extends Controller
{

    public function index($livestreamId)
    {
        $livestream = Livestream::findOrFail($livestreamId);
        $comments = $livestream->comments()->paginate(10);

        return response()->json($comments);
    }


    public function store(Request $request, $livestreamId)
    {
        // dd($request->all());
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $livestream = Livestream::findOrFail($livestreamId);
        $user = auth()->user();

        $comment = new LivestreamComment([
            'user_id' => $user->id,
            'livestream_id' => $livestream->id,
            'comment' => $request->input('comment'),
        ]);

        $comment->save();

        return response()->json($comment, 201);
    }


    public function update(Request $request, $livestreamId, $commentId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $livestream = Livestream::findOrFail($livestreamId);
        $comment = LivestreamComment::findOrFail($commentId);

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->comment = $request->input('comment');
        $comment->save();

        return response()->json($comment);
    }


    public function destroy($livestreamId, $commentId)
    {
        $livestream = Livestream::findOrFail($livestreamId);
        $comment = LivestreamComment::findOrFail($commentId);

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
