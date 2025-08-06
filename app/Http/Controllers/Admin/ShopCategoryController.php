<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use Illuminate\Http\Request;

class ShopCategoryController extends Controller
{
    // List all categories
    public function index()
    {
        $categories = ShopCategory::all();
        return response()->json($categories);
    }

    public function index_view(Request $request)
    {
        $categories = ShopCategory::paginate(10);  // Paginate categories (10 per page)
        return view('admin.shop_category.index', compact('categories'));
    }


    // Store new category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:shop_categories,name|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $slug = $this->generateSlug($request->name);

        $category = ShopCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category
        ], 201);
    }

    public function view_store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:shop_categories,name|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $slug = $this->generateSlug($request->name);

        $category = ShopCategory::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.shop-categories.index')->with('success', 'Category created successfully');
    }

    // Show a single category
    public function show($id)
    {
        $category = ShopCategory::findOrFail($id);
        return response()->json($category);
    }

    // Update existing category
    public function update(Request $request, $id)
    {
        $category = ShopCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:shop_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $slug = $this->generateSlug($request->name);

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category
        ]);
    }

    public function view_update(Request $request, $id)
    {
        $category = ShopCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:shop_categories,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $slug = $this->generateSlug($request->name);

        $category->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

         return redirect()->route('admin.shop-categories.index')->with('success', 'Category updated successfully');
    }

    // Delete category
    public function destroy($id)
    {
        $category = ShopCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    public function view_destroy($id)
    {
        $category = ShopCategory::findOrFail($id);
        $category->delete();

       return redirect()->route('admin.shop-categories.index')->with('success', 'Category deleted successfully');
    }


    // Slug generator
    private function generateSlug($string)
    {
        return strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $string));
    }
}
