<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ShortVideo;
use Illuminate\Http\Request;

class VendorShortVideoController extends Controller
{
    // Show videos
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
