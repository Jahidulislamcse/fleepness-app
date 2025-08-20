<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follower;

class UserVendorFollowController extends Controller
{
    // Follow a vendor
    public function follow($vendor_id)
    {
        $user_id = auth()->id();

        // Check if already following
        $existingFollow = Follower::where('follower_id', $user_id)
            ->where('vendor_id', $vendor_id)
            ->first();

        if ($existingFollow) {
            return response()->json([
                'message' => 'You are already following this vendor.'
            ], 400);
        }

        // Create a new follow entry
        Follower::create([
            'follower_id' => $user_id,
            'vendor_id' => $vendor_id,
        ]);

        return response()->json([
            'message' => 'Vendor followed successfully.'
        ], 201);
    }

    // Unfollow a vendor
    public function unfollow($vendor_id)
    {
        $user_id = auth()->id();

        $follow = Follower::where('follower_id', $user_id)
            ->where('vendor_id', $vendor_id)
            ->first();

        if (!$follow) {
            return response()->json([
                'message' => 'You are not following this vendor.'
            ], 400);
        }

        $follow->delete();

        return response()->json([
            'message' => 'Vendor unfollowed successfully.'
        ], 200);
    }

    // Get all vendors the logged-in user is following
    public function following(Request $request)
    {
        $user_id = auth()->id();

        // Fetch all vendors the user is following
        $following = Follower::with('vendor') // eager load vendor info
            ->where('follower_id', $user_id)
            ->get();

        // Transform data
        $followingData = $following->map(function ($item) {
            $vendor = $item->vendor;
            return [
                'id' => $vendor->id,
                'name' => $vendor->name ?? $vendor->shop_name,
                'email' => $vendor->email,
                'banner_image' => $vendor->banner_image ? asset($vendor->banner_image) : null,
                'cover_img' => $vendor->cover_img ? asset($vendor->cover_img) : null,
                'followed_at' => $item->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'following' => $followingData,
        ]);
    }

    // Get all users following the logged-in user
    public function followers(Request $request)
    {
        $user_id = auth()->id();

        // Fetch all followers of the logged-in user
        $followers = Follower::with('follower') // eager load follower info
            ->where('vendor_id', $user_id)
            ->get();

        // Transform data
        $followersData = $followers->map(function ($item) {
            $follower = $item->follower;
            return [
                'id' => $follower->id,
                'name' => $follower->name,
                'email' => $follower->email,
                'profile_img' => $follower->profile_img ? asset($follower->profile_img) : null,
                'followed_at' => $item->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'followers' => $followersData,
        ]);
    }

}
