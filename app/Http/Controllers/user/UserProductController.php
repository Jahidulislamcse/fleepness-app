<?php

namespace App\Http\Controllers\user;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Database\Eloquent\Casts\Json;
use App\Http\Resources\ProductAndCategorySearchResultResource;

class UserProductController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (! $query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        [$products, $categories] = Concurrency::run([
            fn () => Product::whereLike('name', "%{$query}%")
                ->orWhereLike('short_description', "%{$query}%")
                ->orWhereLike('long_description', "%{$query}%")
                ->orWhereLike('slug', "%{$query}%")
                ->limit(10)
                ->get(),
            fn () => Category::whereLike('name', "%{$query}%")
                ->limit(10)
                ->get(),
        ]);

        return ProductAndCategorySearchResultResource::make($products)
            ->withCategories($categories);
    }

    public function getProductByTag($tag)
    {
        $products = Product::whereLike('tags', '%"'.$tag.'"%')->get();

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'products' => $products->toResourceCollection(),
        ]);
    }

    public function getProductsByType(Request $request, $vendor)
    {
        $recentLimit = 5;
        $type = $request->query('type');
        $perPage = $request->query('per_page', 10);

        if (! $type || ! in_array($type, ['recent', 'low_price'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid type. Allowed values: recent, low_price',
            ], 400);
        }

        $query = Product::with('images')
            ->where('user_id', $vendor)
            ->whereNull('deleted_at');

        if ('recent' === $type) {
            $products = $query
                ->latest()
                ->take($recentLimit)
                ->get();
        } else {
            $products = $query
                ->orderByRaw('COALESCE(discount_price, selling_price) ASC')
                ->paginate($perPage);
        }

        $productsData = 'recent' === $type ?
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

        if ('low_price' === $type) {
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
            'images' => $product->images->map(fn ($image) => $image->path),
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

        if (! is_numeric($minPrice) || ! is_numeric($maxPrice) || $minPrice > $maxPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid price range',
            ], 400);
        }

        $products = Product::where('user_id', $vendor)
            ->where(function ($query) use ($minPrice, $maxPrice) {
                $query->whereBetween('discount_price', [$minPrice, $maxPrice])
                    ->orWhere(function ($q) use ($minPrice, $maxPrice) {
                        $q->where(function ($q2) {
                            $q2->whereNull('discount_price')
                                ->orWhere('discount_price', 0);
                        })
                            ->whereBetween('selling_price', [$minPrice, $maxPrice]);
                    });
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products->toResourceCollection(),
        ], 200);
    }

    public function getProductsInPriceCategory(Request $request, $vendor)
    {
        $sort = $request->query('category', 'low'); 

        $productsQuery = Product::where('user_id', $vendor);

        if ($sort === 'low') {
            $productsQuery->orderByRaw("COALESCE(discount_price, selling_price) ASC");
        } elseif ($sort === 'high') {
            $productsQuery->orderByRaw("COALESCE(discount_price, selling_price) DESC");
        }

        $products = $productsQuery->get();

        return $products->toResourceCollection()->additional([
            'success' => true,
        ]);
    }


    public function show(Product $product)
    {
        $product->load([
            'user',
            'reviews',
            'sizes',
            'images',
            'category',
            'tag' => [
                'parent',
                'grandParent',
            ],
        ]);

        return ProductResource::make($product)->additional([
            'success' => true,
        ]);
    }

    public function getAllProducts($vendor, Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $products = Product::with('images')
            ->where('user_id', $vendor)->latest()
            ->paginate($perPage);

        return $products->toResourceCollection();
    }

    public function getSimilarProducts($id)
    {
        $product = Product::with('category', 'images')
            ->whereNull('deleted_at')
            ->find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $tags = $product->tags;
        $tagId = $tags ? $tags[0] : null;

        if (! $tagId) {
            return response()->json(['message' => 'No tag found for this product'], 404);
        }

        $filteredProducts = Product::with(['category', 'images'])
            ->whereNull('deleted_at')
            ->where('id', '!=', $id)
            ->whereNotNull('tags')
            ->whereRaw('JSON_CONTAINS(tags, ?)', [Json::encode((string) $tagId)])
            ->get();

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

    }
}
