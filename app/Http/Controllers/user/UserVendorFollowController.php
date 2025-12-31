<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follower;

class UserVendorFollowController extends Controller
{
    public function toggle($vendor_id)
    {
        $user_id = auth()->id();

        $follow = Follower::where('follower_id', $user_id)
            ->where('vendor_id', $vendor_id)
            ->first();

        if ($follow) {
            $follow->delete();

            return response()->json([
                'status' => 'unfollowed',
                'message' => 'Vendor unfollowed successfully.'
            ], 200);
        }

        Follower::create([
            'follower_id' => $user_id,
            'vendor_id' => $vendor_id,
        ]);

        return response()->json([
            'status' => 'followed',
            'message' => 'Vendor followed successfully.'
        ], 201);
    }


    public function following(Request $request)
    {
        $user_id = auth()->id();

        $following = Follower::with('vendor') 
            ->where('follower_id', $user_id)
            ->get();

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

    public function followers(Request $request)
    {
        $user_id = auth()->id();

        $followers = Follower::with('follower') 
            ->where('vendor_id', $user_id)
            ->get();

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
