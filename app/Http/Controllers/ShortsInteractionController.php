<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShortCommentResource;
use App\Models\ShortsComment;
use App\Models\ShortsLike;
use App\Models\ShortsSave;
use App\Models\ShortVideo;
use Illuminate\Http\Request;
use App\Http\Resources\ShortsProductResource;
use App\Http\Resources\ShortVideoResource;

class ShortsInteractionController extends Controller
{
    
    public function allshorts()
    {
        $videos = ShortVideo::with('user')
            ->latest()
            ->cursorPaginate(10);

        $videos->through(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'video' => $video->video,
                'likes_count' => $video->likes_count,
                'created_at' => $video->created_at,
                'user' => [
                    'id' => $video->user->id,
                    'name' => $video->user->name,
                    'banner_image' => $video->user->banner_image,
                    'cover_image' => $video->user->cover_image,
                ],
            ];
        });

        return response()->json($videos);
    }


    public function getShortProducts($shortId)
    {
        $video = ShortVideo::find($shortId);

        if (!$video) {
            return response()->json(['message' => 'Short video not found'], 404);
        }

        $products = $video->products()->with('images')->get();

        return ShortsProductResource::collection($products);
    }

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

    public function getComments($shortId)
    {
        $short = ShortVideo::find($shortId);

        if (!$short) {
            return response()->json(['message' => 'Short video not found'], 404);
        }

        $comments = $short->comments()->with('user')->latest()->get();

        return ShortCommentResource::collection($comments);
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
            $short->decrement('likes_count');
            $liked = false;
        } else {
            ShortsLike::create([
                'user_id' => auth()->id(),
                'short_video_id' => $shortId,
            ]);
            $short->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'message' => $liked ? 'Short liked.' : 'Like removed.',
            'liked' => $liked,
            'like_count' => $short->likes_count, 
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

    public function getSavedShorts()
    {
        $userId = auth()->id();

        $savedShorts = ShortsSave::where('user_id', $userId)
            ->with('shortVideo.user', 'shortVideo.products.images')
            ->latest()
            ->get()
            ->pluck('shortVideo');

        $result = $savedShorts->map(function ($video) {
            return [
                'id' => $video->id,
                'title' => $video->title,
                'video' => $video->video,
                'likes_count' => $video->likes_count,
                'created_at' => $video->created_at,
                'user' => [
                    'id' => $video->user->id,
                    'name' => $video->user->name,
                    'banner_image' => $video->user->banner_image,
                    'cover_image' => $video->user->cover_image,
                ],
                'products' => $video->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'short_description' => $product->short_description,
                        'images' => $product->images->map(fn($img) => $img->path),
                    ];
                }),
            ];
        });

        return response()->json($result);
    }


}
