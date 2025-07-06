<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getCategories()
    {
        $categories = Category::with(['children' => function ($query) {
                $query->orderBy('order', 'asc')
                     ->with('children');  
            }])
            ->whereNull('parent_id')
            ->orderBy('order', 'asc')
            ->get(['id', 'name', 'profile_img', 'cover_img']);


        return response()->json([
            'status' => true,
            'categories' => $categories,
        ]);
    }
}
