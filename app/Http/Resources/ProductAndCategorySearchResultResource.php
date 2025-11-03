<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @property Collection<int,Product> $collection
 */
class ProductAndCategorySearchResultResource extends ResourceCollection
{
    private Collection $categories;

    public static $wrap = null;

    public function withCategories(Collection $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'products' => $this->collection->toResourceCollection(),
            'categories' => $this->categories->toResourceCollection(),
        ];
    }
}
