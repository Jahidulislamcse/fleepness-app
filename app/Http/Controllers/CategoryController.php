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

    public function getCategoriesWithTags()
    {
        try {
            // Fetch all top-level categories (parent categories)
            $categories = Category::whereNull('parent_id')->get();

            // Iterate over each category and include its children as sub-categories, and the children's children as tags
            $categoriesWithTags = $categories->map(function($category) {
                return $this->getCategoryWithTags($category);
            });

            return response()->json([
                'success' => true,
                'categories' => $categoriesWithTags,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getCategoryWithTags($category)
    {
        // Get the direct children of the current category (sub-categories)
        $children = Category::where('parent_id', $category->id)->get();

        // Iterate over children and fetch their own children as tags
        $category->sub_categories = $children->map(function($child) {
            return $this->getCategoryTags($child);  // Get tags for this child
        });

        return $category;
    }

    private function getCategoryTags($category)
    {
        // Get the children (tags) of the current child category
        $tags = Category::where('parent_id', $category->id)->get();

        // Assign the tags to this category
        $category->tags = $tags;

        return $category;
    }

}
