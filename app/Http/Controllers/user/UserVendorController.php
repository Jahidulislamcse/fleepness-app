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
        $query = $request->input('query');

        $vendorsQuery = User::where('role', 'vendor')
            ->where('status', 'approved')
            ->orderByDesc('order_count')
            ->select('id', 'name', 'email', 'shop_name', 'order_count', 'cover_image', 'description');

        if ($query) {
            $vendorsQuery->where('shop_name', 'LIKE', "%{$query}%");
        }

        $paginated = $vendorsQuery->distinct()->paginate($perPage);

        return response()->json([
            'success' => true,
            'vendors' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
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

        $reviewPercentage = $reviews->count() > 0 ? round(($reviews->where('rating', '>=', 4)->count() / $reviews->count()) * 100) : 0;

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
            'product_count' => $products->count(),
            'videos' => $videos,
            'reviews_count' => $reviews->count(),
            'review_percentage' => $reviewPercentage,
            'reviews' => $reviews,
            'follower_count' => $followers->count(),
            'followers' => $followers,
            'tags' => $tags,
        ], 200);
    }

    public function similarSellers($vendor)
    {
        // Fetch the vendor's shop category
        $vendorInfo = User::select('shop_category')->find($vendor);

        if (!$vendorInfo) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found'
            ], 404);
        }

        // Fetch other vendors with the same shop category
        $similarVendors = User::where('shop_category', $vendorInfo->shop_category)
            ->where('id', '!=', $vendor) // Exclude the current vendor
            ->select('id', 'shop_name', 'phone_number', 'banner_image', 'cover_image', 'total_sales')
            ->with('shopCategory:id,name')
            ->get();

        // Return the similar vendors as a response
        return response()->json([
            'status' => true,
            'message' => 'Similar vendors retrieved successfully',
            'similar_vendors' => $similarVendors,
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
