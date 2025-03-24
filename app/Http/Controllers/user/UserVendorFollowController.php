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
}
