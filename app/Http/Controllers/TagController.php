<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
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
            // Fetch products where the tag ID is in the 'tags' JSON column
            $products = Product::whereJsonContains('tags', $id)->get();

            // Return response as JSON or normal view depending on request
                return response()->json([
                    'success' => true,
                    'products' => $products
                ]);
        } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
        }
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
