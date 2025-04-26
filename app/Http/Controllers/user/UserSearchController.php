<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        // Search in Products table
        $sellers = User::where('shop_name', 'LIKE', "%{$query}%")
            ->where('status', 'approved')
            ->select('name', 'shop_name')
            ->limit(10)
            ->get();

        // Search in Categories table
        $tags = Category::whereNotNull('parent_id')  
            ->where('name', 'LIKE', "%{$query}%")
            ->select('name', 'store_title')
            ->limit(10)
            ->get();

        return response()->json([
            'sellers' => $sellers,
            'tags' => $tags,
        ]);
    }
}
