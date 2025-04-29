<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getCategories()
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'categories' => $categories,
        ]);
    }
}
