<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ShortsProduct;
use App\Models\ShortVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorShortVideoController extends Controller
{
    public function index_api()
    {
        $userId = auth()->id();

        $videos = ShortVideo::where('user_id', $userId)
            ->latest()
            ->paginate(10);

        return response()->json($videos);
    }




    public function store_api(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'video' => 'required|file|mimes:mp4,mov,avi|max:10240',
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $path = null;
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $path = $file->store('videos/shorts');
        }

        $video = ShortVideo::create([
            'title' => $request->title,
            'video' => $path,
            'user_id' => auth()->id(),
        ]);

        $products = [];
        if ($request->has('product_ids')) {
            foreach ($request->product_ids as $productId) {
                $sp = ShortsProduct::create([
                    'short_video_id' => $video->id,
                    'product_id' => $productId,
                ]);

                $product = $sp->product()->first(); 
                if ($product) {
                $products[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'short_description' => $product->short_description,
                    'images' => $product->images->map(fn($img) => asset($img->path)),
                ];
            }
            }
        }

        return response()->json([
            'message' => 'Short video uploaded successfully.',
            'data' => [
                'video' => $video,
                'products' => $products,
            ],
        ], 201);
    }


    public function show_api($id)
    {
        $video = ShortVideo::find($id);
        if (!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        $products = $video->products()->with('product.images')->get()->map(function ($sp) {
            $product = $sp->product;
            return [
                'id' => $product->id,
                'name' => $product->name,
                'short_description' => $product->short_description,
                'images' => $product->images->map(fn($img) => asset($img->path)),
            ];
        });

        return response()->json([
            'video' => $video,
            'products' => $products,
        ]);
    }


    public function update_api(Request $request, $id)
    {
        $video = ShortVideo::find($id);
        if (!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        if ($video->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'video' => 'sometimes|required|file|mimes:mp4,mov,avi|max:10240',
            'product_ids' => 'sometimes|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('title')) {
            $video->title = $request->title;
        }

        if ($request->hasFile('video')) {
            if ($video->video && \Storage::exists($video->video)) {
                \Storage::delete($video->video);
            }

            $path = $request->file('video')->store('videos/shorts');
            $video->video = $path;
        }

        $video->save();

        $products = [];
        if ($request->has('product_ids')) {
            foreach ($request->product_ids as $productId) {
                $sp = ShortsProduct::firstOrCreate([
                    'short_video_id' => $video->id,
                    'product_id' => $productId,
                ]);

                $product = $sp->product()->first();
                if ($product) {
                    $products[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'short_description' => $product->short_description,
                        'images' => $product->images->map(fn($img) => asset($img->path)),
                    ];
                }
            }
        } else {
            $products = $video->products()->with('product.images')->get()->map(function ($sp) {
                $product = $sp->product;
                return [
                    'name' => $product->name,
                    'short_description' => $product->short_description,
                    'images' => $product->images->map(fn($img) => asset($img->path)),
                ];
            });
        }

        return response()->json([
            'message' => 'Short video updated successfully.',
            'data' => [
                'video' => $video,
                'products' => $products,
            ],
        ]);
    }


    public function destroy_api($id)
    {
        $video = ShortVideo::find($id);
        if (!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        if ($video->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($video->video && \Storage::exists($video->video)) {
            \Storage::delete($video->video);
        }

        ShortsProduct::where('short_video_id', $video->id)->delete();

        $video->delete();

        return response()->json(['message' => 'Short video deleted successfully.']);
    }



    // *** Show videos in Web view ***
    public function Videos()
    {
        $data['shorts'] = ShortVideo::where('user_id', auth()->id())->get();
        return view('vendor.media.short-videos', $data);
    }

    // Add a new video
    public function store(Request $request)
    {
        $request->validate([
            'video' => 'required|mimes:mp4,avi,mov|max:51200', // 50MB max
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $video = $request->file('video');
        $name_gen = hexdec(uniqid()) . '.' . $video->getClientOriginalExtension();

        // Define storage path
        $path = public_path('upload/videos');

        // Create directory if not exists
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Move video to the desired folder
        $video->move($path, $name_gen);

        // Save video details in the database
        $video_url = 'upload/videos/' . $name_gen;

        ShortVideo::create([
            'user_id' => auth()->id(),
            'video' => $video_url,
            'title' => $request->title,
            'alt_text' => $request->alt_text,
        ]);

        return redirect()->back()->with('success', 'Video uploaded successfully!');
    }



    // Update video details
    public function update(Request $request, $id)
    {
        $video = ShortVideo::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $video->update([
            'title' => $request->title,
            'alt_text' => $request->alt_text,
        ]);

        return redirect()->back()->with('success', 'Video details updated!');
    }

    // Delete video
    public function destroy($id)
    {
        $video = ShortVideo::where('user_id', auth()->id())->findOrFail($id);
        $video->delete();

        return redirect()->back()->with('success', 'Video deleted successfully!');
    }
}
