<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Follower;
use App\Models\Product;
use App\Models\SellerTags;
use App\Models\ShortVideo;
use App\Models\User;
use App\Models\VendorReview;
use Illuminate\Http\Request;

class UserVendorController extends Controller
{
    public function vendorlist(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $paginated = User::where('role', 'vendor')
            ->orderByDesc('order_count')
            ->select('id', 'name', 'email', 'order_count', 'cover_image')
            ->paginate($perPage);

        return response()->json([
            'success'    => true,
            'vendors'    => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ], 200);
    }
    public function vendorData($vendor)
    {
        // Fetch vendor basic info
        $vendorInfo = User::select(
            'id',
            'shop_name',
            'shop_category',
            'phone_number',
            'banner_image',
            'cover_image',
            'address',
            'contact_number',
            'total_sales'
        )->with('shopCategory:id,name')->find($vendor);

        if (!$vendorInfo) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        // Fetch products
        $products = Product::where('user_id', $vendor)->get();

        // Fetch videos
        $videos = ShortVideo::where('user_id', $vendor)->get();

        // Fetch reviews
        $reviews = VendorReview::where('vendor_id', $vendor)->get();

        // Fetch followers
        $followers = Follower::where('vendor_id', $vendor)->get();

        // Fetch tags
        $sellerTags = SellerTags::where('vendor_id', $vendor)->first();
        $tags = [];

        if ($sellerTags && is_array($sellerTags->tags)) {
            $tags = Category::whereIn('id', $sellerTags->tags)
                ->get();
        }

        // Return response
        return response()->json([
            'status' => true,
            'message' => 'Seller data retrieved successfully',
            'vendor_info' => $vendorInfo,
            'products' => $products,
            'videos' => $videos,
            'reviews' => $reviews,
            'follower_count' => $followers->count(),
            'followers' => $followers,
            'tags' => $tags,
        ], 200);
    }

    public function getShortVideos($vendor)
    {
        // Fetch videos where user_id matches the given id
        $videos = ShortVideo::where('user_id', $vendor)->get();

        // Check if videos exist
        if ($videos->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No videos found for this vendor',
                'data' => []
            ], 404);
        }

        // Return success response
        return response()->json([
            'status' => true,
            'message' => 'Videos retrieved successfully',
            'data' => $videos
        ], 200);
    }
}
