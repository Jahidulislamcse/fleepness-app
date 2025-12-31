<?php

namespace App\Http\Controllers;

use App\Http\Resources\LivestreamSaveResource;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\ShortsSave;
use App\Models\LivestreamSave;
use App\Models\Livestream;


class SavedContentController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        return match ($type) {
            'shorts' => $this->savedShorts(),
            'lives'  => $this->savedLivestreams(),
            default  => response()->json([
                'message' => 'Invalid type. Use shorts or lives.'
            ], 400),
        };
    }

    protected function savedShorts()
    {
        $userId = auth()->id();

        $savedShorts = ShortsSave::where('user_id', $userId)
            ->with('shortVideo.user', 'shortVideo.products.images')
            ->latest()
            ->get()
            ->pluck('shortVideo')
            ->map(function ($video) {
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
                    'products' => $video->products->map(fn ($product) => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'short_description' => $product->short_description,
                        'images' => $product->images->pluck('path'),
                    ]),
                ];
            });

        return response()->json([
            'status' => 'success',
            'type' => 'shorts',
            'data' => $savedShorts,
        ]);
    }

    protected function savedLivestreams()
    {
        $saves = LivestreamSave::where('user_id', auth()->id())
            ->with([
                'livestream.vendor:id,shop_name,cover_image',
                'livestream' => fn ($q) => $q->withCount([
                    'likes',
                    'comments',
                    'participants as total_participants',
                ]),
            ])
            ->get();

        return response()->json([
            'status' => 'success',
            'type' => 'lives',
            'data' => LivestreamSaveResource::collection($saves),
        ]);
    }
}