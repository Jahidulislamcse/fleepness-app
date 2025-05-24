<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SellerTags;
use App\Models\Product;

class TagController extends Controller
{
    public function getTags(Request $request)
    {
        $categoryId = $request->category_id;
        $tags = Category::where('parent_id', $categoryId)->get();
        return response()->json($tags);
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
        // Fetch the seller tags for the user
        $sellerTag = SellerTags::where('vendor_id', $userId)->first();

        if (!$sellerTag || empty($sellerTag->tags)) {
            return response()->json(['message' => 'No tags found for this user.'], 404);
        }

        // Assuming tags are stored as a JSON or array
        $tags = $sellerTag->tags ?? [];

        // Flatten the array of tags and count their occurrences
        $tagCounts = array_count_values($tags);

        // Sort the tags by frequency in descending order
        arsort($tagCounts);

        // Get the top 3 most used tags (only the keys, which are the tag names)
        $mostUsedTags = array_keys(array_slice($tagCounts, 0, 3, true));

        // Return the top 3 most used tags only (no counts)
        return response()->json([
            'user_id' => $userId,
            'most_used_tags' => $mostUsedTags
        ]);
    }

        public function getTagInfo(Request $request, $id)
    {
        $tag = Category::findOrFail($id);
        return response()->json($tag);
    }

    public function getTagsRandom(Request $request)
    {
        $tags = Category::whereNotNull('parent_id')
            ->inRandomOrder()
            ->get(['id', 'name', 'profile_img', 'cover_img']);

        return response()->json($tags);
    }
}
