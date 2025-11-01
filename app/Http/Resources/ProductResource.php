<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Product $resource
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $tags = Json::decode($this->tags, true);
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

            $tagName = Category::where('id', $tag)->first('name')?->name; // Fetch the tag name
        }

        return [
            $this->getKeyName() => $this->getKey(),

            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'quantity' => $this->quantity,
            'order_count' => $this->order_count,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'deleted_at' => $this->deleted_at,
            'status' => $this->status,
            'admin_approval' => $this->admin_approval,
            'description' => $this->long_description,
            'category_name' => $categoryName,
            'tag_name' => $tagName,

            'user' => $this->whenLoaded('user', new UserResource($this->user)),
            'category' => $this->whenLoaded('category', new CategoryResource($this->category)),
            'tags' => $this->tags,
            'size_template' => $this->whenLoaded('sizeTemplate', new SizeTemplateResource($this->sizeTemplate)),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
