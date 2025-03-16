<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class UserProductController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        // Search in Products table
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('short_description', 'LIKE', "%{$query}%")
            ->orWhere('long_description', 'LIKE', "%{$query}%")
            ->orWhere('slug', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        // Search in Categories table
        $categories = Category::where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function getProductsByPriceRange(Request $request, $vendor)
    {
        // Get minPrice and maxPrice from query parameters
        $minPrice = $request->query('minPrice', 0); // Default to 0 if not provided
        $maxPrice = $request->query('maxPrice', 999999); // Default to a high number if not provided

        // Validate price inputs
        if (!is_numeric($minPrice) || !is_numeric($maxPrice) || $minPrice > $maxPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid price range'
            ], 400);
        }

        // Fetch products where discount_price OR selling_price is within the range
        $products = Product::where('user_id', $vendor)
            ->where(function ($query) use ($minPrice, $maxPrice) {
                $query->whereBetween('discount_price', [$minPrice, $maxPrice])
                    ->orWhere(function ($query) use ($minPrice, $maxPrice) {
                        $query->whereNull('discount_price')
                            ->orWhere('discount_price', 0)
                            ->whereBetween('selling_price', [$minPrice, $maxPrice]);
                    });
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }
}
