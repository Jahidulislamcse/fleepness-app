<?php

namespace App\Http\Controllers;

use App\Models\ShortsComment;
use App\Models\ShortsLike;
use App\Models\ShortsSave;
use App\Models\ShortVideo;
use Illuminate\Http\Request;

class ShortsInteractionController extends Controller
{

    public function comment(Request $request, $shortId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $short = ShortVideo::find($shortId);
        if (!$short) {
            return response()->json(['message' => 'Short video not found.'], 404);
        }

        $comment = ShortsComment::create([
            'user_id' => auth()->id(),
            'short_video_id' => $shortId,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Comment added successfully.',
            'data' => $comment,
        ], 201);
    }


    public function deleteComment($id)
    {
        $comment = ShortsComment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found.'], 404);
        }

        if ($comment->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully.']);
    }


    public function toggleLike($shortId)
    {
        $short = ShortVideo::find($shortId);
        if (!$short) {
            return response()->json(['message' => 'Short video not found.'], 404);
        }

        $like = ShortsLike::where('user_id', auth()->id())
            ->where('short_video_id', $shortId)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            ShortsLike::create([
                'user_id' => auth()->id(),
                'short_video_id' => $shortId,
            ]);
            $liked = true;
        }

        $likeCount = ShortsLike::where('short_video_id', $shortId)->count();

        return response()->json([
            'message' => $liked ? 'Short liked.' : 'Like removed.',
            'liked' => $liked,
            'like_count' => $likeCount,
        ]);
    }


    public function toggleSave($shortId)
    {
        $short = ShortVideo::find($shortId);
        if (!$short) {
            return response()->json(['message' => 'Short video not found.'], 404);
        }

        $save = ShortsSave::where('user_id', auth()->id())
            ->where('short_video_id', $shortId)
            ->first();

        if ($save) {
            $save->delete();
            $saved = false;
        } else {
            ShortsSave::create([
                'user_id' => auth()->id(),
                'short_video_id' => $shortId,
            ]);
            $saved = true;
        }

        $saveCount = ShortsSave::where('short_video_id', $shortId)->count();

        return response()->json([
            'message' => $saved ? 'Short saved.' : 'Save removed.',
            'saved' => $saved,
            'save_count' => $saveCount,
        ]);
    }


    public function getComments($shortId)
    {
        $short = ShortVideo::find($shortId);
        if (!$short) {
            return response()->json(['message' => 'Short video not found.'], 404);
        }

        $comments = ShortsComment::with('user:id,name')
            ->where('short_video_id', $shortId)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Comments fetched successfully.',
            'data' => $comments,
        ]);
    }
}
