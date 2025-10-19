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

        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('short_description', 'LIKE', "%{$query}%")
            ->orWhere('long_description', 'LIKE', "%{$query}%")
            ->orWhere('slug', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

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

    public function getProductsByType(Request $request, $vendor)
    {
        $type = $request->query('type');
        $perPage = $request->query('per_page', 10); 
        $recentLimit = 5; 

        if (!$type || !in_array($type, ['recent', 'low_price'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type. Allowed values: recent, low_price'
            ], 400);
        }

        $query = Product::with('images')
            ->where('user_id', $vendor)
            ->whereNull('deleted_at');

        if ($type === 'recent') {
            $products = $query->orderBy('created_at', 'desc')
                            ->take($recentLimit)
                            ->get();
        } else { 
            $products = $query->orderByRaw('COALESCE(discount_price, selling_price) ASC')
                            ->paginate($perPage);
        }

        $productsData = $type === 'recent' ?
            $products->map(function ($product) {
                return $this->transformProduct($product);
            }) :
            $products->getCollection()->map(function ($product) {
                return $this->transformProduct($product);
            });

        $response = [
            'success' => true,
            'type' => $type,
            'products' => $productsData,
        ];


        if ($type === 'low_price') {
            $response['pagination'] = [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ];
        }

        return response()->json($response);
    }


    private function transformProduct($product)
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'category_id' => $product->category_id,
            'size_template_id' => $product->size_template_id,
            'tags' => $product->tags,
            'slug' => $product->slug,
            'code' => $product->code,
            'quantity' => $product->quantity,
            'selling_price' => $product->selling_price,
            'discount_price' => $product->discount_price,
            'short_description' => $product->short_description,
            'long_description' => $product->long_description,
            'images' => $product->images->map(fn($image) => $image->path),
            'tags_data' => $product->tagCategories()->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'store_title' => $tag->store_title,
                    'slug' => $tag->slug,
                    'profile_img' => $tag->profile_img ? asset($tag->profile_img) : null,
                    'cover_img' => $tag->cover_img ? asset($tag->cover_img) : null,
                    'description' => $tag->description,
                ];
            }),
        ];
    }

    public function getProductsByPriceRange(Request $request, $vendor)
    {
        $minPrice = $request->query('minPrice', 0); 
        $maxPrice = $request->query('maxPrice', 999999); 

        if (!is_numeric($minPrice) || !is_numeric($maxPrice) || $minPrice > $maxPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid price range'
            ], 400);
        }

        $products = Product::where('user_id', $vendor)
            ->where(function ($query) use ($minPrice, $maxPrice) {
                $query->whereBetween('discount_price', [$minPrice, $maxPrice])
                    ->orWhere(function($q) use ($minPrice, $maxPrice) {
                        $q->where(function($q2) {
                            $q2->whereNull('discount_price')
                                ->orWhere('discount_price', 0);
                        })
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

        if ($category === 'low') {
            $minPrice = 1;
            $maxPrice = 500;
        } elseif ($category === 'medium') {
            $minPrice = 501;
            $maxPrice = 1000;
        } elseif ($category === 'premium') {
            $minPrice = 1001;
            $maxPrice = PHP_INT_MAX; 
        } else {
            return response()->json(['message' => 'Invalid category'], 400);
        }

        if (!is_numeric($minPrice) || !is_numeric($maxPrice) || $minPrice > $maxPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid price range'
            ], 400);
        }

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

        $productData = $product->toArray();
        $productData['user'] = $transformedUser;
        $productData['category'] = $transformedCategory;


        $productData['images'] = $product->images->map(function($image) {
            return asset($image->path); 
        });

        $productData['tags_data'] = $product->tagCategories();


        return response()->json([
            'success' => true,
            'product' => $productData,
        ]);
    }



    public function getAllProducts($vendor, Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $products = Product::with('images')
            ->where('user_id', $vendor)
            ->paginate($perPage);

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category_id' => $product->category_id,
                    'size_template_id ' => $product->size_template_id ,
                    'tags' => $product->tags,
                    'slug ' => $product->slug ,
                    'code ' => $product->code ,
                    'quantity' => $product->quantity,
                    'selling_price' => $product->selling_price,
                    'discount_price' => $product->discount_price,
                    'short_description' => $product->short_description,
                    'long_description' => $product->long_description,
                    'images' => $product->images->map(fn($image) => $image->path), // only image path
                    'tags_data' => $product->tagCategories()->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'store_title' => $tag->store_title,
                            'slug' => $tag->slug,
                            'profile_img' => $tag->profile_img ? asset($tag->profile_img) : null,
                            'cover_img' => $tag->cover_img ? asset($tag->cover_img) : null,
                            'description' => $tag->description,
                        ];
                    }),
                ];
            }),
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
        $product = Product::with('category', 'images')
            ->whereNull('deleted_at')
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $tags = json_decode($product->tags, true) ?: [];
        $tagId = $tags ? $tags[0] : null; 

        if (!$tagId) {
            return response()->json(['message' => 'No tag found for this product'], 404);
        }

        $similarProducts = Product::with(['category', 'images'])
            ->whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->get(); 

        $filteredProducts = $similarProducts->filter(function ($product) use ($tagId) {
            $tags = json_decode($product->tags, true) ?: [];
            return in_array($tagId, $tags); 
        })->values(); 

        if ($filteredProducts->isEmpty()) {
            return response()->json(['message' => 'No similar products found'], 404);
        }

        $similarProductsData = $filteredProducts->map(function ($product) {
            $productData = $product->only(['name', 'selling_price', 'discount_price', 'long_description']);

            $productData['images'] = $product->images->map(function ($image) {
                return $image->path;  
            });

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
