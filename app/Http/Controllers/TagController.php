<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Setting;
use App\Models\SellerTags;
use App\Models\Product;

class TagController extends Controller
{

    public function getTags(Request $request)
    {
        $category_id = $request->input('category_id');

        if (!$category_id) {
            return response()->json([
                'status' => false,
                'message' => 'Category ID is required',
            ], 400);
        }

        $category = Category::find($category_id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $children = Category::where('parent_id', $category_id)->get();

        $grandchildren = [];

        foreach ($children as $child) {
            $childGrandchildren = $child->children()->get()->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'profile_img' => $tag->profile_img ? asset($tag->profile_img) : null,
                    'cover_img' => $tag->cover_img ? asset($tag->cover_img) : null,
                    'description' => $tag->description,
                    'store_title' => $tag->store_title,
                ];
            })->toArray();

            $grandchildren = array_merge($grandchildren, $childGrandchildren);
        }

        return response()->json([
            'status' => true,
            'message' => 'Tags fetched successfully',
            'tags' => $grandchildren,
        ]);
    }




    public function getTagsByCategory($category_id)
    {
        // Find the category to ensure it exists
        $category = Category::find($category_id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Get all children (subcategories) of the selected category
        $children = Category::where('parent_id', $category_id)->get();

        // Prepare an array to hold all grandchildren
        $grandchildren = [];

        // For each child, get its own children (grandchildren)
        foreach ($children as $child) {
            // Retrieve grandchildren (children of this child)
            $grandchildren = array_merge($grandchildren, $child->children()->get()->toArray());
        }

        // Return the grandchildren as a JSON response
        return response()->json([
            'status' => true,
            'message' => 'Grandchildren fetched successfully',
            'tags' => $grandchildren,
        ]);
    }


    public function getProductByTag($id)
    {
        try {
            $perPage = 10;
            $currentPage = request()->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;

            // Fetch tag name
            $tag = Category::find($id);
            $tagName = $tag ? $tag->name : null;

            // Fetch all products with images
            $products = Product::with('images')
                ->whereNull('deleted_at')
                ->where('status', 'active')
                ->get()
                ->filter(function ($product) use ($id) {
                    $tags = json_decode($product->tags, true) ?: [];
                    return in_array($id, $tags);
                })
                ->values();

            $paginatedProducts = $products->slice($offset, $perPage)->map(function ($product) {
                // Transform image paths to full URLs
                $product->images->transform(function ($image) {
                    $image->path = url($image->path);
                    return $image;
                });

                return $product;
            });

            $totalPages = ceil($products->count() / $perPage);
            $nextPageUrl = $currentPage < $totalPages ? url()->current() . '?page=' . ($currentPage + 1) : null;
            $previousPageUrl = $currentPage > 1 ? url()->current() . '?page=' . ($currentPage - 1) : null;

            return response()->json([
                'success' => true,
                'tag_id' => (int) $id,
                'tag_name' => $tagName,
                'products' => $paginatedProducts->values(),
                'pagination' => [
                    'current_page' => $currentPage,
                    'per_page' => $perPage,
                    'total_pages' => $totalPages,
                    'total_products' => $products->count(),
                    'next_page_url' => $nextPageUrl,
                    'previous_page_url' => $previousPageUrl,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function getOwnProductByTag($id)
    {
        try {
            // Fetch all products and filter by tag ID
            $products = Product::whereNull('deleted_at')->where('user_id', auth()->id())
                ->get()
                ->filter(function ($product) use ($id) {
                    $tags = json_decode($product->tags, true) ?: [];
                    return in_array($id, $tags);
                })
                ->values();

            // Pagination settings
            $perPage = 10;
            $currentPage = request()->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;

            // Slice the products for the current page
            $paginatedProducts = $products->slice($offset, $perPage);

            // Add tag names to the products
            $paginatedProducts = $paginatedProducts->map(function ($product) {
                $tags = json_decode($product->tags, true) ?: [];
                $tagNames = Category::whereIn('id', $tags)->pluck('name')->toArray();
                $product->tag_names = implode(', ', $tagNames);

                return $product;
            });

            // Calculate total pages
            $totalPages = ceil($products->count() / $perPage);

            // Generate next and previous page URLs
            $nextPageUrl = $currentPage < $totalPages ? url()->current() . '?page=' . ($currentPage + 1) : null;
            $previousPageUrl = $currentPage > 1 ? url()->current() . '?page=' . ($currentPage - 1) : null;

            return response()->json([
                'success' => true,
                'products' => $paginatedProducts,
                'pagination' => [
                    'current_page' => $currentPage,
                    'per_page' => $perPage,
                    'total_pages' => $totalPages,
                    'total_products' => $products->count(),
                    'next_page_url' => $nextPageUrl,
                    'previous_page_url' => $previousPageUrl,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }






    public function getMostUsedTags($userId)
    {
        $sellerTag = SellerTags::where('vendor_id', $userId)->first();

        if (!$sellerTag || empty($sellerTag->tags)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        $raw = $sellerTag->tags;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $tagsArr = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $tagsArr = $raw;
        } else {
            $tagsArr = [];
        }

        if (count($tagsArr) === 0) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        $tagCounts = array_count_values($tagsArr);
        arsort($tagCounts);
        $mostUsedTagIds = array_keys(array_slice($tagCounts, 0, 3, true));

        if (empty($mostUsedTagIds)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        $tags = Category::whereIn('id', $mostUsedTagIds)
            ->get(['id', 'name', 'profile_img', 'cover_img', 'store_title', 'description']);

        $transformedTags = $tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'profile_img' => $tag->profile_img ? asset($tag->profile_img) : null,
                'cover_img' => $tag->cover_img ? asset($tag->cover_img) : null,
                'store_title' => $tag->store_title,
                'description' => $tag->description,
            ];
        });

        return response()->json([
            'user_id'        => $userId,
            'most_used_tags' => $transformedTags,
        ]);
    }


   public function getAllUsedTags($userId)
    {
        $sellerTag = SellerTags::where('vendor_id', $userId)->first();

        if (!$sellerTag || empty($sellerTag->tags)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        $raw = $sellerTag->tags;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $tagsArr = is_array($decoded) ? $decoded : [];
        } elseif (is_array($raw)) {
            $tagsArr = $raw;
        } else {
            $tagsArr = [];
        }

        if (count($tagsArr) === 0) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        $tagsArr = array_unique($tagsArr);

        $tags = Category::whereIn('id', $tagsArr)
            ->get(['id', 'name', 'profile_img', 'cover_img']);

        if ($tags->isEmpty()) {
            return response()->json(['message' => 'No valid tags found for this user.'], 404);
        }

        $transformedTags = $tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'profile_img' => $tag->profile_img ? asset($tag->profile_img) : null,
                'cover_img' => $tag->cover_img ? asset($tag->cover_img) : null,
            ];
        });

        return response()->json([
            'user_id'   => $userId,
            'used_tags' => $transformedTags
        ]);
    }





    public function getTagInfo(Request $request, $id)
    {
        $tag = Category::findOrFail($id);
        return response()->json($tag);
    }

    public function getTagsRandom(Request $request)
    {
        $setting = Setting::first() ?? new Setting();

        $limit = (int) $setting->num_of_tag;

        if ($limit < 1) {
            return response()->json([]);
        }

        $tags = Category::whereNotNull('parent_id')
            ->where('mark', 'T')
            ->inRandomOrder()
            ->take($limit)
            ->get(['id', 'name', 'profile_img', 'cover_img']);

        $transformedTags = $tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
                'profile_img' => $tag->profile_img ? asset($tag->profile_img) : null,
                'cover_img' => $tag->cover_img ? asset($tag->cover_img) : null,
            ];
        });

        return response()->json($transformedTags);
    }


}
