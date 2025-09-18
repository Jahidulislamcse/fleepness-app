<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $path = null;
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $path = $file->store('videos/shorts');
            // $filename = time() . '.' . $file->getClientOriginalExtension();
            // $file->move(public_path('videos/shorts'), $filename);
            // $path = 'videos/shorts/' . $filename;
        }

        $video = ShortVideo::create([
            'title' => $request->title,
            'video' => $path,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Short video uploaded successfully.',
            'data' => $video,
        ], 201);
    }

    public function show_api($id)
    {
        $video = ShortVideo::find($id);
        if (!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }
        return response()->json($video);
    }

    public function update_api(Request $request, $id)
    {
        $video = ShortVideo::find($id);
        if (!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        // Authorization check: only owner can update
        if ($video->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'video' => 'sometimes|required|file|mimes:mp4,mov,avi|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('title')) {
            $video->title = $request->title;
        }

        if ($request->hasFile('video')) {
            // Optionally delete old file here

            $file = $request->file('video');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('videos/shorts'), $filename);
            $video->video = 'videos/shorts/' . $filename;
        }

        $video->save();

        return response()->json([
            'message' => 'Short video updated successfully.',
            'data' => $video,
        ]);
    }


    public function destroy_api($id)
    {
        $video = ShortVideo::find($id);
        if (!$video) {
            return response()->json(['message' => 'Video not found.'], 404);
        }

        // Authorization check: only owner can delete
        if ($video->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Optionally delete the physical video file here
        // Example:
        // if (file_exists(public_path($video->video_path))) {
        //     unlink(public_path($video->video_path));
        // }

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
