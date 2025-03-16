<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class UserVendorController extends Controller
{
    public function vendorlist()
    {
        // Fetch vendors sorted by order_count in descending order
        $vendors = User::where('role', 'vendor')
            ->orderByDesc('order_count')
            ->select('id', 'name', 'email', 'order_count', 'profile_image') // Select only needed fields
            ->get();

        // Return JSON response for React Native
        return response()->json([
            'success' => true,
            'vendors' => $vendors
        ], 200);
    }

    public function vendorData($vendor)
    {
        // Fetch products where user_id matches the given id
        $products = Product::where('user_id', $vendor)->get();

        // Check if products exist
        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No products found for this vendor',
                'data' => []
            ], 404);
        }

        // Return success response
        return response()->json([
            'status' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ], 200);
    }
}
