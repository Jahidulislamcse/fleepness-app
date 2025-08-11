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

    public function getProductByTag($tag)
    {
        $products = Product::where('tags', 'LIKE', '%"' . $tag . '"%')->get();

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'products' => $products,
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

    public function getProductsInPriceCategory(Request $request, $vendor)
    {
        $category = $request->query('category');

        // Define price ranges based on category
        if ($category === 'low') {
            $minPrice = 1;
            $maxPrice = 500;
        } elseif ($category === 'medium') {
            $minPrice = 501;
            $maxPrice = 1000;
        } elseif ($category === 'premium') {
            $minPrice = 1001;
            $maxPrice = PHP_INT_MAX; // No upper limit
        } else {
            return response()->json(['message' => 'Invalid category'], 400);
        }

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
   public function show($id)
    {
        $product = Product::with([
                'user',
                'reviews',
                'sizes',
                'images',
                'category',
            ])
            ->whereNull('deleted_at')
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Transform user info
        $user = $product->user;
        $transformedUser = [
            'id' => $user->id,
            'shop_name' => $user->shop_name,
            'banner_image' => $user->banner_image ? asset($user->banner_image) : null,
            'cover_image' => $user->cover_image ? asset($user->cover_image) : null,
        ];

        $category = $product->category;
        $transformedCategory = [
            'name' => $category->name,
        ];

        // Prepare product data
        $productData = $product->toArray();
        $productData['user'] = $transformedUser;
        $productData['category'] = $transformedCategory;


        $productData['images'] = $product->images->map(function($image) {
            return asset($image->path); // Convert image path to full URL
        });


        return response()->json([
            'success' => true,
            'product' => $productData,
        ]);
    }



    public function getAllProducts($vendor, Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $products = Product::where('user_id', $vendor)
            ->paginate($perPage);

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
        ]);
    }

public function getSimilarProducts($id)
{
    try {
        // Fetch the current product by ID
        $product = Product::with('category', 'images')
            ->whereNull('deleted_at')
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Get the tag ID of the current product
        $tags = json_decode($product->tags, true) ?: [];
        $tagId = $tags ? $tags[0] : null; // Assuming only one tag per product

        // If no tag is found, return an error
        if (!$tagId) {
            return response()->json(['message' => 'No tag found for this product'], 404);
        }

        // Fetch all products with the same tag (excluding the current one)
        $similarProducts = Product::with(['category', 'images'])
            ->whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->get(); // Only fetch products, no need for whereRaw

        // Filter products based on matching tags
        $filteredProducts = $similarProducts->filter(function ($product) use ($tagId) {
            $tags = json_decode($product->tags, true) ?: [];
            return in_array($tagId, $tags); // Check if the tagId exists in the tags array
        })->values(); // Reset keys after filtering

        // If no similar products are found
        if ($filteredProducts->isEmpty()) {
            return response()->json(['message' => 'No similar products found'], 404);
        }

        // Prepare the product data for the response
        $similarProductsData = $filteredProducts->map(function ($product) {
            $productData = $product->only(['name', 'selling_price', 'discount_price', 'long_description']);

            // Add images for the product
            $productData['images'] = $product->images->map(function ($image) {
                return $image->path;  // Convert image path to a full URL
            });

            // Add category name for the product
            $productData['category_name'] = $product->category ? $product->category->name : null;

            return $productData;
        });

        return response()->json([
            'success' => true,
            'similar_products' => $similarProductsData,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
}








}
