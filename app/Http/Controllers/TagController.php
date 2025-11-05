<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Category;
use App\Models\SellerTags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Casts\Json;

class TagController extends Controller
{
    public function getTags(Request $request)
    {
        $category_id = $request->input('category_id');

        if (! $category_id) {
            return response()->json([
                'status' => false,
                'message' => 'Category ID is required',
            ], 400);
        }

        $category = Category::find($category_id);

        if (! $category) {
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

        if (! $category) {
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
        // Fetch tag name
        $tag = Category::find($id);
        $tagName = $tag ? $tag->name : null;

        // Fetch all products with images
        $products = Product::with(['images', 'sizeTemplate' => ['items']])
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->whereNotNull('tags')
            ->whereRaw('JSON_CONTAINS(products.tags, ?)', [Json::encode((string) $id)])
            ->paginate();

        return $products->toResourceCollection()->additional([
            'success' => true,
            'tag_name' => $tagName,
            'tag_id' => (int) $id,
        ]);

    }

    public function getOwnProductByTag($id)
    {
        // Fetch all products and filter by tag ID
        $products = Product::whereNull('deleted_at')
            ->where('user_id', Auth::id())
            ->whereNotNull('tags')
            ->whereRaw('JSON_CONTAINS(tags, ?)', [Json::encode((string) $id)])
            ->paginate();

        return $products->toResourceCollection()
            ->additional([
                'success' => true,
            ]);
    }

    public function getMostUsedTags($userId)
    {
        $sellerTag = SellerTags::where('vendor_id', $userId)->first();

        if (! $sellerTag || empty($sellerTag->tags)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        $tagsArr = $sellerTag->tags;

        if (empty($tagsArr)) {
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
            'user_id' => $userId,
            'most_used_tags' => $transformedTags,
        ]);
    }

    public function getAllUsedTags($userId)
    {
        $sellerTag = SellerTags::where('vendor_id', $userId)->first();

        if (! $sellerTag || empty($sellerTag->tags)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        $tagsArr = $sellerTag->tags;

        if (empty($tagsArr)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        $tagsArr = array_unique($tagsArr);

        $tags = Category::whereIn('id', $tagsArr)
            ->get(['id', 'name', 'profile_img', 'cover_img']);

        if ($tags->isEmpty()) {
            return response()->json(['message' => 'No valid tags found for this user.'], 404);
        }

        return response()->json([
            'user_id' => $userId,
            'used_tags' => $tags->toResourceCollection(),
        ]);
    }

    public function getTagInfo(Request $request, $id)
    {
        $tag = Category::findOrFail($id);

        return response()->json($tag);
    }

    public function getTagsRandom(Request $request)
    {
        $setting = Setting::first() ?? new Setting;

        $limit = (int) $setting->num_of_tag;

        if (1 > $limit) {
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
