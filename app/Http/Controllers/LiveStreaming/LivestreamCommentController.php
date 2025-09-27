<?php

namespace App\Http\Controllers\LiveStreaming;

use App\Http\Controllers\Controller;
use App\Models\Livestream;
use App\Models\LivestreamComment;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;

class LivestreamCommentController extends Controller
{
    public function index($livestreamId)
    {
        $livestream = Livestream::findOrFail($livestreamId);
        $comments = $livestream->comments()->paginate(10);

        return response()->json($comments);
    }

    public function store(Request $request, $livestreamId, #[CurrentUser] User $user)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $livestream = Livestream::findOrFail($livestreamId);

        $comment = new LivestreamComment([
            'user_id' => $user->getKey(),
            'livestream_id' => $livestream->getKey(),
            'comment' => $request->input('comment'),
        ]);

        $comment->save();

        $comment->notifyParticipants();

        return response()->json($comment, 201);
    }

    public function update(Request $request, $livestreamId, $commentId, #[CurrentUser] User $user)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment = LivestreamComment::findOrFail($commentId);

        if ($comment->user()->isNot($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->comment = $request->input('comment');
        $comment->save();

        return response()->json($comment);
    }

    public function destroy($livestreamId, $commentId, #[CurrentUser] User $user)
    {
        $comment = LivestreamComment::findOrFail($commentId);

        if ($comment->user()->isNot($user)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
