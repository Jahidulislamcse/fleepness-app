<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Category;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $tags = json_decode($this->tags, true);
        $tagName = null;
        $categoryName = null;

        if ($tags) {
            $tag = $tags[0]; // Get the first tag (if available)

            // Fetch the category name based on the tag ID
            $tagCategory = Category::where('id', $tag)->first();
            if ($tagCategory) {
                $category = $tagCategory;

                // Find the parent category of the tag's category
                $parentCategory = Category::where('id', $category->parent_id)->first();

                if ($parentCategory) {
                    // Find the parent category of the parent category
                    $grandParentCategory = Category::where('id', $parentCategory->parent_id)->first();

                    if ($grandParentCategory) {
                        // Set the final grandparent category as the product's category
                        $categoryName = $grandParentCategory->name;
                    }
                }
            }

            $tagName = Category::where('id', $tag)->pluck('name')->first(); // Fetch the tag name
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->long_description,
            'short_description' => $this->short_description,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'category_name' => $categoryName,
            'tag_name' => $tagName,
        ];
    }
}
