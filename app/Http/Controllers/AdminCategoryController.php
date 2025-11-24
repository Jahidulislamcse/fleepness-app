<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


class AdminCategoryController extends Controller
{
    public function index()
        {
            $categories = Category::with(['children' => function ($query) {
                $query->orderBy('order', 'asc');
            }])
            ->whereNull('parent_id')
            ->orderBy('order', 'asc')
            ->get();

            return view('admin.categories.index', compact('categories'));
        }

        public function getChildren($parentId)
        {
            $parent = Category::find($parentId);
            return response()->json($parent->children);
        }


    // public function create()
    // {
    //     $categories = Category::whereNull('parent_id')->get(); // Get top-level categories
    //     return view('categories.create', compact('categories'));
    // }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('parent_id', $request->parent_id);
                }),
            ],
            'store_title' => 'nullable|string',
            'mark' => 'nullable|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'profile_img' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp,bmp|max:2048',
            'cover_img' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp,bmp|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'parent_id', 'store_title','mark',]);

        if ($request->parent_id) {
            $maxOrder = Category::where('parent_id', $request->parent_id)->max('order');
        } else {
            $maxOrder = Category::whereNull('parent_id')->max('order');
        }

        $data['order'] = $maxOrder ? $maxOrder + 1 : 1; 

        if ($request->hasFile('profile_img')) {
            $data['profile_img'] = $this->uploadImage($request->file('profile_img'), 'category_images');
        }

        if ($request->hasFile('cover_img')) {
            $data['cover_img'] = $this->uploadImage($request->file('cover_img'), 'category_images');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }


    private function uploadImage(UploadedFile $image, $folder)
    {
        return $image->store('upload/'.$folder);
    }




    public function edit(Category $category)
    {
        $categories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id) 
            ->get();

        $parentCategory = $category->parent;

        $grandChildCategory = $parentCategory ? $parentCategory->parent : null;

        return view('admin.categories.category_edit', compact('category', 'categories', 'parentCategory', 'grandChildCategory'));
    }


    public function update(Request $request, Category $category)
    {
    //    dd($request->all());
        // Validate the incoming data
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request, $category) {
                    return $query->where('parent_id', $request->parent_id)->where('id', '!=', $category->id);
                }),
            ],
            'store_title' => 'nullable|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'profile_img' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048',
            'cover_img' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048',
            'order' => 'nullable|integer|min:1',
        ]);

        // Store previous order & parent_id
        $previousOrder = $category->order;
        $previousParentId = $category->parent_id;

        // Update category fields
        $category->name = $request->name;
        $category->description = $request->description;
        $category->parent_id = $request->parent_id;
        $category->store_title = $request->store_title;

        // Handle profile image
        if ($request->hasFile('profile_img')) {
            if ($category->profile_img && file_exists(public_path($category->profile_img))) {
                unlink(public_path($category->profile_img));
            }
            $category->profile_img = $this->uploadImage($request->file('profile_img'), 'category_images/');
        }

        // Handle cover image
        if ($request->hasFile('cover_img')) {
            if ($category->cover_img && file_exists(public_path($category->cover_img))) {
                unlink(public_path($category->cover_img));
            }
            $category->cover_img = $this->uploadImage($request->file('cover_img'), 'category_images/');
        }

        // Reorder categories if the order is updated
        if ($request->filled('order') && $request->order != $previousOrder) {
            $this->reorderCategories($category, $previousOrder, $request->order, $previousParentId);
        }

        $category->order = $request->order;
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }



    /**
     * Function to reorder categories while keeping main and child categories separate.
     */
    private function reorderCategories($category, $previousOrder, $newOrder, $previousParentId)
    {
        $isChild = $category->parent_id !== null;

        // Determine the correct query scope
        $query = Category::where('parent_id', $isChild ? $category->parent_id : null);

        // Shift orders accordingly
        if ($newOrder > $previousOrder) {
            // Shift down: Move categories in between up
            $query->where('order', '>', $previousOrder)
                ->where('order', '<=', $newOrder)
                ->where('id', '!=', $category->id)
                ->decrement('order');
        } else {
            // Shift up: Move categories in between down
            $query->where('order', '<', $previousOrder)
                ->where('order', '>=', $newOrder)
                ->where('id', '!=', $category->id)
                ->increment('order');
        }
    }





    public function destroy(Category $category)
    {
        $subCategory = Category::where('parent_id', $category->id)->count('id');
        if ($subCategory > 0) {
            return redirect()->route('admin.categories.index')->with('success', 'You can not deleted this category. Please First Delete all Child Category!');
        }
        // $product = Product::where('category_id', $category->id)->count('id');
        // if ($product > 0) {
        //     return redirect()->route('categories.index')->with('success', 'You can not deleted this category.Please First Delete all Product under this Subcategory!');
        // }
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
