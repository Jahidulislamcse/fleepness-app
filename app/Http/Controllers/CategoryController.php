<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getCategories(){
    $categories = Category::with(['children' => function ($query) {
            $query->orderBy('order', 'asc')
                ->with(['children' => function ($subQuery) {
                    $subQuery->orderBy('order', 'asc');
                }]);
        }])
        ->whereNull('parent_id')
        ->orderBy('order', 'asc')
        ->get(['id', 'name', 'profile_img', 'cover_img']);

    $transformed = $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'subcategories' => $category->children->map(function ($subcat) {
                return [
                    'id' => $subcat->id,
                    'name' => $subcat->name,
                    'tags' => $subcat->children->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'profile_img' => $tag->profile_img,
                            'cover_img' => $tag->cover_img,
                            'description' => $tag->description,
                            'store_title' => $tag->store_title,
                        ];
                    }),
                ];
            }),
        ];
    });

    return response()->json([
        'status' => true,
        'categories' => $transformed,
    ]);
    }


    public function getCategoriesOnly()
    {

        $categories = Category::whereNull('parent_id')->get();

        $transformed = $categories->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
            ];
        });
            return response()->json([
            'status' => true,
            'categories' => $transformed,
        ]);
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
