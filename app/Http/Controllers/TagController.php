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
        $categoryId = $request->category_id;
        $tags = Category::where('parent_id', $categoryId)->where('mark', 'T')->get();
        return response()->json($tags);
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
            $products = Product::all()->filter(function ($product) use ($id) {
                $tags = json_decode($product->tags, true) ?: [];
                return in_array($id, $tags);
            })->values();

            return response()->json([
                'success' => true,
                'products' => $products,
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
        // 1) Fetch the seller tags row for this vendor
        $sellerTag = SellerTags::where('vendor_id', $userId)->first();

        if (!$sellerTag || empty($sellerTag->tags)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        // 2) Ensure $tagsArr is always an array
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

        // 3) Count occurrences of each tag ID
        $tagCounts = array_count_values($tagsArr);

        // 4) Sort by frequency descending
        arsort($tagCounts);

        // 5) Take the top 3 tag IDs
        $mostUsedTagIds = array_keys(array_slice($tagCounts, 0, 3, true));

        if (empty($mostUsedTagIds)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        // 6) Fetch full Category records for those tag IDs
        $tags = Category::whereIn('id', $mostUsedTagIds)->get();

        // 7) Return user ID + full tag data
        return response()->json([
            'user_id'        => $userId,
            'most_used_tags' => $tags
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

    $tags = Category::whereNotNull('parent_id')->where('mark', 'T')
        ->inRandomOrder()
        ->take($limit)
        ->get(['id', 'name', 'profile_img', 'cover_img']);

    return response()->json($tags);
}

}
