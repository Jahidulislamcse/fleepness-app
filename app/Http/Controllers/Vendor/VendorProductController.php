<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\ProductImage;
use App\Models\ProductSize;
use App\Models\Stock;
use App\Models\SizeTemplate;
use App\Models\SizeTemplateItem;
use App\Models\SellerTags;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VendorProductController extends Controller
{
    public function index()
    {
        $data['products'] = Product::with('category')->latest()->whereNull('deleted_at')->where('user_id', auth()->id())->get();
        $data['categories'] = Category::whereNull('parent_id')->get();
        $data['size_templates'] = SizeTemplate::where('seller_id', auth()->id())->get();
        return view('vendor.products.index', $data);
    }

    public function getAllMyProducts(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $perPage = $request->input('per_page', 10);

        $products = Product::with([
                'category',
                'images',
                'sizes',
                'sizeTemplate.items'
            ])
            ->latest()
            ->whereNull('deleted_at')
            ->where('user_id', auth()->id())
            ->paginate($perPage);

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }

        $products->getCollection()->transform(function ($product) {
            $data = $product->toArray();
            $data['tags_data'] = $product->tagCategories();
            return $data;
        });

        return response()->json([
            'success' => true,
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
        ]);
    }

    public function getMyProducts()
    {
       $user = User::find(23);
        dd($user);

        $products = Product::with([
                'category',
                'sizes',
                'sizeTemplate.items'
            ])
            ->latest()
            ->whereNull('deleted_at')
            ->where('user_id', $user->id())
            ->get();

        // If you also want to append full tag data:
        $products->transform(function($product) {
            $data = $product->toArray();

            // Add resolved categories for tags (if any)
            $data['tags_data'] = $product->tagCategories();

            return $data;
        });

        return response()->json([
            'success'  => true,
            'products' => $products,
        ]);
    }

    public function getSingleProduct($id)
    {
        $product = Product::with([
                'images',
                'category',
                'sizes',
                'sizeTemplate.items'
            ])
            ->whereNull('deleted_at')
            ->where('user_id', auth()->id())
            ->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $data = $product->toArray();

        // Add resolved categories for tags (if any)
        $data['tags_data'] = $product->tagCategories();

        return response()->json([
            'success' => true,
            'product' => $data,
        ]);
    }



    public function search(Request $request)
    {
        $query = $request->input('query');
        $perPage = $request->input('per_page', 5);

        if (!$query) {
            return response()->json(['message' => 'Query parameter is required'], 400);
        }

        $products = Product::where('user_id', auth()->id())
            ->where(function($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('short_description', 'LIKE', "%{$query}%")
                            ->orWhere('long_description', 'LIKE', "%{$query}%")
                            ->orWhere('slug', 'LIKE', "%{$query}%");
            })
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
                'next_page_url' => $products->nextPageUrl(),
                'prev_page_url' => $products->previousPageUrl(),
            ],
        ]);
    }








    protected function generateUniqueCode()
    {
        do {
            $code = random_int(100000, 999999);
        } while (Product::where('code', $code)->exists());

        return $code;
    }


    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'long_description' => 'nullable|string',
                'short_description' => 'nullable|string',
                'size_template_id' => 'nullable|exists:size_templates,id',
                'quantity' => 'nullable|integer|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
            ]);

            // Set additional fields
            $validated['code'] = $this->generateUniqueCode();
            $validated['status'] = 'active';
            $validated['admin_approval'] = 'approved';
            $validated['user_id'] = auth()->id();
            $validated['tags'] = json_encode($request->tags);

            // Create the product
            $product = Product::create($validated);

            // Copy sizes from the template if size_template_id is provided
            if ($request->size_template_id) {
                $sizes = SizeTemplateItem::where('template_id', $request->size_template_id)->get();
                foreach ($sizes as $size) {
                    ProductSize::create([
                        'product_id' => $product->id,
                        'size_name' => $size->size_name,
                        'size_value' => $size->size_value,
                    ]);
                }
            }

            // Save images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $photo) {
                    $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
                    $path = public_path('upload/product');

                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    $photo->move($path, $name_gen);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => 'upload/product/' . $name_gen,
                        'alt_text' => $request->input('alt_text', ''),
                    ]);
                }
            }

            // Save tags
            if (!empty($request->tags)) {
                $sellerTag = SellerTags::firstOrNew([
                    'vendor_id' => auth()->id(),
                ]);

                $sellerTag->tags = array_merge($sellerTag->tags ?? [], $request->tags);
                $sellerTag->save();
            }

            DB::commit();

            // Process the tags and categories
            $tags = json_decode($product->tags, true);
            $tag = $tags ? $tags[0] : null;

            $tagName = null;
            $categoryId = null;

            if ($tag) {
                // Get tag name and category ID
                $tagName = Category::where('id', $tag)->pluck('name')->first();
                $tagCategory = Category::where('id', $tag)->first();

                if ($tagCategory) {
                    // Find the parent category
                    $parentCategory = Category::where('id', $tagCategory->parent_id)->first();

                    if ($parentCategory) {
                        // Find the grandparent category
                        $grandParentCategory = Category::where('id', $parentCategory->parent_id)->first();

                        if ($grandParentCategory) {
                            // Set the category ID to the grandparent category
                            $categoryId = $grandParentCategory->id;
                        }
                    }
                }
            }

            // Update the product with the correct category_id (store the ID in the database)
            if ($categoryId) {
                $product->update([
                    'category_id' => $categoryId,
                ]);
            }

            // Fetch the product sizes
            $productSizes = ProductSize::where('product_id', $product->id)->get();
            $images = ProductImage::where('product_id', $product->id)->get();


            // Fetch the category name based on the category_id
            $categoryName = $categoryId ? Category::where('id', $categoryId)->pluck('name')->first() : null;

            // Return the product with category name in the response
            return response()->json([
                'success' => true,
                'message' => 'Product Created successfully',
                'product' => $product,
                'sizes' => $productSizes,
                'images' =>  $images,
                'tag' => $tagName,
                'category_name' => $categoryName,  // Send the category name in the response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function edit(Product $product)
    {
        // dd($product);
        $productImageCount = ProductImage::where('product_id', $product->id)->count();
        $categories = Category::whereNull('parent_id')->get();
        return view('vendor.products.product_edit', compact('product', 'categories', 'productImageCount'));
    }

   public function update(Request $request, $id)
    {
        // dd($request->all());
        try {
            // Find the product
            $product = Product::findOrFail($id);

            // Check if the product belongs to the logged-in user
            if ($product->user_id !== auth()->id()) {
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => 'You are not authorized to update this product.'], 403)
                    : redirect()->back()->with('error', 'You are not authorized to update this product.');
            }

            // Validate input
            $validated = $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'name' => 'nullable|string|max:255',
                'long_description' => 'nullable|string',
                'short_description' => 'nullable|string',
                'size_template_id' => 'nullable|exists:size_templates,id',
                'quantity' => 'nullable|integer|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'discount_price' => 'nullable|numeric|min:0',
            ]);

            // Update the product details
            $validated['tags'] = json_encode($request->tags);
            $product->update($validated);

            // Handle sizes (retain only submitted size IDs)
            if ($request->has('sizes')) {
                $submittedSizes = $request->sizes;

                foreach ($submittedSizes as $sizeData) {
                    $sizeData['size_name'] = strtolower($sizeData['size_name']);  // Convert size name to lowercase

                    // Check if the size already exists for the product
                    $size = ProductSize::where('product_id', $product->id)
                        ->where('size_name', $sizeData['size_name'])
                        ->first();

                    // If the size exists, update its value if available is true
                    if ($size) {
                        if ($sizeData['available'] === 'false') {
                            // If available is false, delete the size
                            $size->delete();
                        } else {
                            // Update the size value
                            $size->size_value = $sizeData['size_value'];
                            $size->save();
                        }
                    } else {
                        if ($sizeData['available'] === 'true') {
                            ProductSize::create([
                                'product_id' => $product->id,
                                'size_name' => $sizeData['size_name'], // Ensure size name is lowercase
                                'size_value' => $sizeData['size_value'],
                            ]);
                        }
                    }
                }
            }

            // Add new images if any
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $photo) {
                    $name_gen = hexdec(uniqid()) . '.' . $photo->getClientOriginalExtension();
                    $path = public_path('upload/product');

                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }

                    $photo->move($path, $name_gen);

                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => 'upload/product/' . $name_gen,
                        'alt_text' => $request->input('alt_text', ''),
                    ]);
                }
            }

            // 🆕 Save tags to seller_tags table (add new tags only)
            if (!empty($request->tags)) {
                $sellerTag = SellerTags::firstOrNew([
                    'vendor_id' => auth()->id(),
                ]);

                $sellerTag->tags = array_merge($sellerTag->tags ?? [], $request->tags);
                $sellerTag->save();
            }

            $tags = json_decode($product->tags, true);
            $tag = $tags ? $tags[0] : null; // Get the first tag (if available)

            // Fetch the category name based on the tag ID
            $tagName = null;
            $category = null;
            if ($tag) {
                $tagName = Category::where('id', $tag)->pluck('name')->first(); // Fetch single category name
                $tagCategory = Category::where('id', $tag)->first();

                if ($tagCategory) {
                    // Find the parent category of the tag's category
                    $parentCategory = Category::where('id', $tagCategory->parent_id)->first();

                    if ($parentCategory) {
                        // Find the parent category of the parent category
                        $grandParentCategory = Category::where('id', $parentCategory->parent_id)->first();

                        if ($grandParentCategory) {
                            // Set the final grandparent category as the product's category
                            $category = $grandParentCategory->id;
                        }
                    }
                }
            }
            if ($category) {
                $product->update([
                    'category_id' => $category,
                ]);
            }
            $categoryName = $category ? Category::where('id', $category)->pluck('name')->first() : null;

            $productSizes = ProductSize::where('product_id', $product->id)->get();

            return response()->json(['success' => true,
            'message' => 'Product updated successfully',
            'product' => $product,
            'sizes' => $productSizes,
            'tag' => $tagName,
            'category_name' => $categoryName,
            ]);

            } catch (\Exception $e) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
    }







    public function destroy($id, Request $request)
    {
        $product = Product::findOrFail($id);
        // dd($product);

        $order = OrderItem::where('product_id', $product->id)->count();

        if ($order > 0) {
            $message = 'You cannot delete this product because it has related orders. Please delete the orders first.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 403);
            }

            return redirect()->route('vendor.products.index')->with('error', $message);
        }

        $product->update([
            'deleted_at' => now(),
            'status' => 'inactive',
        ]);

        $message = 'Product deleted successfully.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'product' => $product,
            ]);
        }

        return redirect()->route('vendor.products.index')->with('success', $message);
    }

    public function inactive(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'status' => 'inactive',
        ]);

        $message = 'Product inactivated successfully.';


        return response()->json([
            'success' => true,
            'message' => $message,
            'product' => $product,
        ]);
    }

    public function active(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'status' => 'active',
        ]);

        $message = 'Product activated successfully.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'product' => $product,
        ]);
    }


    public function ImageDelete($id)
    {
        $data = ProductImage::find($id);
        if (!$data) {
            return response()->json(['error' => 'Image not found.'], 404);
        }

        if (file_exists(public_path($data->path))) {
            unlink(public_path($data->path));
        }

        $data->delete();
        return back();
    }


    public function StockDelete($id)
    {
        $data = Stock::find($id);
        if (file_exists($data->photo)) {
            unlink(public_path($data->photo));
        }
        $data->delete();

        $notification = array(
            'message' => 'Data Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
