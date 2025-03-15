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
}
