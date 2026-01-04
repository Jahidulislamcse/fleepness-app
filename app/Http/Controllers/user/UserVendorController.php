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
            'description',
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
        $product_count = Product::where('user_id', $vendor)->count();

        // Fetch reviews
        $reviews = VendorReview::where('vendor_id', $vendor)->get();
        $reviews_count = rand(0, 500);
        $review_percentage = $reviews_count > 0
            ? number_format(mt_rand(33, 50) / 10, 2)
            : 0;

        $reviewPercentage = $reviews->count() > 0 ? round(($reviews->where('rating', '>=', 4)->count() / $reviews->count()) * 100) : 0;

        // Fetch followers
        $followers = Follower::where('vendor_id', $vendor)->get();

        // Fetch tags
        $sellerTags = SellerTags::where('vendor_id', $vendor)->first();


        // Return response
        return response()->json([
            'status' => true,
            'message' => 'Seller data retrieved successfully',
            'vendor_info' => $vendorInfo,
            'product_count' => $product_count,
            'reviews_count' => $reviews_count,
            'review_percentage' => $review_percentage,
            'reviews' => $reviews,
            'follower_count' => $followers->count(),
            'followers' => $followers,
        ], 200);
    }

    public function similarSellers($vendor)
    {
        $shopCategoryId = User::where('id', $vendor)->value('shop_category');

        if (! $shopCategoryId) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $similarVendors = User::where('shop_category', $shopCategoryId)
            ->where('id', '!=', $vendor)
            ->select(
                'id',
                'shop_name',
                'phone_number',
                'banner_image',
                'cover_image',
                'total_sales',
                'shop_category' 
            )
            ->with('shopCategory:id,name')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Similar vendors retrieved successfully',
            'similar_vendors' => $similarVendors,
        ]);
    }



    public function getShortVideos($vendor)
    {
        $videos = ShortVideo::where('user_id', $vendor)->latest()->paginate(10);

        if ($videos->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No videos found for this vendor',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Videos retrieved successfully',
            'data' => $videos
        ], 200);
    }

}
