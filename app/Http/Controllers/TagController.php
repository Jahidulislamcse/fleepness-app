<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;


class TagController extends Controller
{
    public function getTags(Request $request)
    {
        $categoryId = $request->category_id;
        $tags = Category::where('parent_id', $categoryId)->get();
        return response()->json($tags);
    }

    public function getTagsRandom(Request $request)
    {
        $tags = Category::whereNotNull('parent_id')
            ->inRandomOrder()
            ->get(['id', 'name', 'profile_img', 'cover_img']);

        return response()->json($tags);
    }
}
