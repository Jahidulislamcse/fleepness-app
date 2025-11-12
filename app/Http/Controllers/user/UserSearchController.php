<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $perPage = $request->input('per_page', 10); 

        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        $sellers = User::where('shop_name', 'LIKE', "%{$query}%")
            ->where('status', 'approved')
            ->select('name', 'shop_name', 'shop_category', 'banner_image', 'cover_image', 'description')
            ->paginate($perPage);

        $tags = Category::where('mark', 'T')
            ->where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'store_title', 'profile_img', 'cover_img', 'description')
            ->paginate($perPage)
            ->through(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'store_title' => $tag->store_title,
                    'description' => $tag->description,
                    'profile_img' => $tag->profile_img ? asset($tag->profile_img) : null,
                    'cover_img' => $tag->cover_img ? asset($tag->cover_img) : null,
                ];
            });

        $products = Product::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('short_description', 'LIKE', "%{$query}%")
                ->orWhere('long_description', 'LIKE', "%{$query}%");
            })
            ->with('images') 
            ->select('id', 'name', 'slug', 'short_description', 'selling_price', 'discount_price')
            ->paginate($perPage)
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'short_description' => $product->short_description,
                    'selling_price' => $product->selling_price,
                    'discount_price' => $product->discount_price,
                    'images' => $product->images->map(fn($img) => asset($img->path)),
                ];
            });

        return response()->json([
            'sellers' => $sellers,
            'tags' => $tags,
            'products' => $products,
        ]);
    }

}
